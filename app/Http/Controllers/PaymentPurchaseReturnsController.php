<?php
namespace App\Http\Controllers;

use App\Exports\Payment_Purchase_Return_Export;
use App\Mail\PaymentReturn;
use App\Models\PaymentPurchaseReturns;
use App\Models\Provider;
use App\Models\PurchaseReturn;
use App\Models\Role;
use App\Models\Setting;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Twilio\Rest\Client as Client_Twilio;
use DB;
use PDF;

class PaymentPurchaseReturnsController extends BaseController
{

    //------------- Get All Payment Purchase Returns --------------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_purchase_Return', PaymentPurchaseReturns::class);

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        // Filter fields With Params to retriever
        $param = array(0 => 'like', 1 => '=', 2 => 'like' , 3 => '=');
        $columns = array(0 => 'Ref', 1 => 'purchase_return_id', 2 => 'Reglement' , 3 => 'date');
        $data = array();

        // Check If User Has Permission View  All Records
        $Payments = PaymentPurchaseReturns::with('PurchaseReturn', 'PurchaseReturn.provider')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })

        // Multiple Filter
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('provider_id'), function ($query) use ($request) {
                    return $query->whereHas('PurchaseReturn.provider', function ($q) use ($request) {
                        $q->where('id', '=', $request->provider_id);
                    });
                });
            });
        $Filtred = $helpers->filter($Payments, $columns, $param, $request)

        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('Ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('date', 'LIKE', "%{$request->search}%")
                        ->orWhere('Reglement', 'LIKE', "%{$request->search}%")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('PurchaseReturn', function ($q) use ($request) {
                                $q->where('Ref', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('PurchaseReturn.provider', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });

        $totalRows = $Filtred->count();
        $Payments = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($Payments as $Payment) {

            $item['date'] = $Payment->date;
            $item['Ref'] = $Payment->Ref;
            $item['Ref_return'] = $Payment['PurchaseReturn']->Ref;
            $item['provider_name'] = $Payment['PurchaseReturn']['provider']->name;
            $item['Reglement'] = $Payment->Reglement;
            $item['montant'] = number_format($Payment->montant, 2, '.', '');

            $data[] = $item;
        }

        $suppliers = Provider::where('deleted_at', '=', null)->get(['id', 'name']);
        $purchase_returns = PurchaseReturn::get(['Ref', 'id']);

        return response()->json([
            'totalRows' => $totalRows,
            'payments' => $data,
            'purchase_returns' => $purchase_returns,
            'suppliers' => $suppliers,
        ]);
    }

    //----------- Store New Payment Purchase Return --------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', PaymentPurchaseReturns::class);

        if($request['montant'] > 0){
            \DB::transaction(function () use ($request) {
                $role = Auth::user()->roles()->first();
                $view_records = Role::findOrFail($role->id)->inRole('record_view');
                $PurchaseReturn = PurchaseReturn::findOrFail($request['purchase_return_id']);
        
                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === purchase return->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $PurchaseReturn);
                }

                $total_paid = $PurchaseReturn->paid_amount + $request['montant'];
                $due = $PurchaseReturn->GrandTotal - $total_paid;

                if ($due === 0.0 || $due < 0.0) {
                    $payment_statut = 'paid';
                } else if ($due !== $PurchaseReturn->GrandTotal) {
                    $payment_statut = 'partial';
                } else if ($due === $PurchaseReturn->GrandTotal) {
                    $payment_statut = 'unpaid';
                }

                PaymentPurchaseReturns::create([
                    'purchase_return_id' => $request['purchase_return_id'],
                    'Ref' => $this->getNumberOrder(),
                    'date' => $request['date'],
                    'Reglement' => $request['Reglement'],
                    'montant' => $request['montant'],
                    'change' => $request['change'],
                    'notes' => $request['notes'],
                    'user_id' => Auth::user()->id,
                ]);

                $PurchaseReturn->update([
                    'paid_amount' => $total_paid,
                    'payment_statut' => $payment_statut,
                ]);

            }, 10);
        }

        return response()->json(['success' => true, 'message' => 'Payment Create successfully'], 200);
    }

    //------------ function show -----------\\

    public function show($id){
        //
        
    }

    //----------- Update Payment Purchase Return --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', PaymentPurchaseReturns::class);

        \DB::transaction(function () use ($id, $request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $payment = PaymentPurchaseReturns::findOrFail($id);
    
            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === payment->id
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $PurchaseReturn = PurchaseReturn::find($payment->purchase_return_id);
            $old_total_paid = $PurchaseReturn->paid_amount - $payment->montant;
            $new_total_paid = $old_total_paid + $request['montant'];
            $due = $PurchaseReturn->GrandTotal - $new_total_paid;

            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } else if ($due !== $PurchaseReturn->GrandTotal) {
                $payment_statut = 'partial';
            } else if ($due === $PurchaseReturn->GrandTotal) {
                $payment_statut = 'unpaid';
            }
            
            $payment->update([
                'date' => $request['date'],
                'Reglement' => $request['Reglement'],
                'montant' => $request['montant'],
                'change' => $request['change'],
                'notes' => $request['notes'],
            ]);

            $PurchaseReturn->update([
                'paid_amount' => $new_total_paid,
                'payment_statut' => $payment_statut,
            ]);

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Update successfully'], 200);
    }

    //----------- Remove Payment Purchase Return --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', PaymentPurchaseReturns::class);

        
        \DB::transaction(function () use ($id, $request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $payment = PaymentPurchaseReturns::findOrFail($id);
    
            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === payment->id
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $PurchaseReturn = PurchaseReturn::find($payment->purchase_return_id);
            $total_paid = $PurchaseReturn->paid_amount - $payment->montant;
            $due = $PurchaseReturn->GrandTotal - $total_paid;

            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } else if ($due !== $PurchaseReturn->GrandTotal) {
                $payment_statut = 'partial';
            } else if ($due === $PurchaseReturn->GrandTotal) {
                $payment_statut = 'unpaid';
            }

            PaymentPurchaseReturns::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            $PurchaseReturn->update([
                'paid_amount' => $total_paid,
                'payment_statut' => $payment_statut,
            ]);

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Delete successfully'], 200);
    }

    //------------- Send Payment Purchase Return To Email -----------\\

    public function SendEmail(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'view', PaymentPurchaseReturns::class);

        $payment = $request->all();
        $pdf = $this->payment_return($request, $payment['id']);
        $this->Set_config_mail(); // Set_config_mail => BaseController
        $mail = Mail::to($request->to)->send(new PaymentReturn($payment, $pdf));
        return $mail;
    }

    //----------- Number Order Payment Purchase Return --------------\\

    public function getNumberOrder()
    {
        $last = DB::table('payment_purchase_returns')->latest('id')->first();

        if ($last) {
            $item = $last->Ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;

        } else {
            $code = 'INV/RT_1111';
        }
        return $code;
    }

    //----------- Payment Purchase Return PDF --------------\\

    public function payment_return(Request $request, $id)
    {
        $payment = PaymentPurchaseReturns::with('PurchaseReturn', 'PurchaseReturn.provider')->findOrFail($id);

        $payment_data['return_Ref'] = $payment['PurchaseReturn']->Ref;
        $payment_data['supplier_name'] = $payment['PurchaseReturn']['provider']->name;
        $payment_data['supplier_phone'] = $payment['PurchaseReturn']['provider']->phone;
        $payment_data['supplier_adr'] = $payment['PurchaseReturn']['provider']->adresse;
        $payment_data['supplier_email'] = $payment['PurchaseReturn']['provider']->email;
        $payment_data['montant'] = $payment->montant;
        $payment_data['Ref'] = $payment->Ref;
        $payment_data['date'] = $payment->date;
        $payment_data['Reglement'] = $payment->Reglement;

        $helpers = new helpers();
        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->Get_Currency_Code();

        $pdf = \PDF::loadView('pdf.Payment_Purchase_Return', [
            'symbol' => $symbol,
            'setting' => $settings,
            'payment' => $payment_data,
        ]);

        return $pdf->download('Payment_Purchase_Return.pdf');
    }

    //----------- EXPORT TO EXCEL Payment Purchase Returns  --------------\\

    public function exportExcel(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PaymentPurchaseReturns::class);

        return Excel::download(new Payment_Purchase_Return_Export, 'Payment_Purchase_Returns.xlsx');
    }

     //-------------------Sms Notifications -----------------\\
     public function Send_SMS(Request $request)
     {
         $payment = PaymentPurchaseReturns::with('PurchaseReturn', 'PurchaseReturn.provider')->findOrFail($id);

         $url = url('/api/payment_Return_Purchase_PDF/' . $request->id);
         $receiverNumber = $payment['PurchaseReturn']['provider']->phone;
         $message = "Dear" .' '.$payment['PurchaseReturn']['provider']->name." \n We are contacting you in regard to a Payment #".$payment['PurchaseReturn']->Ref.' '.$url.' '. "that has been created on your account. \n We look forward to conducting future business with you.";
         
         try {
   
             $account_sid = env("TWILIO_SID");
             $auth_token = env("TWILIO_TOKEN");
             $twilio_number = env("TWILIO_FROM");
   
             $client = new Client_Twilio($account_sid, $auth_token);
             $client->messages->create($receiverNumber, [
                 'from' => $twilio_number, 
                 'body' => $message]);
     
         } catch (Exception $e) {
             return response()->json(['message' => $e->getMessage()], 500);
         }
     }


}

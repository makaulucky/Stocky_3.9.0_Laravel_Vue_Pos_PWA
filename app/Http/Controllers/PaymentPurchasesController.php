<?php
namespace App\Http\Controllers;

use App\Exports\Payment_Purchase_Export;
use App\Mail\Payment_Purchase;
use App\Models\PaymentPurchase;
use App\Models\Provider;
use App\Models\Purchase;
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

class PaymentPurchasesController extends BaseController
{

    //------------- Get All Payments Purchases --------------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_Purchases', PaymentPurchase::class);

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
        $columns = array(0 => 'Ref', 1 => 'purchase_id', 2 => 'Reglement', 3 =>'date');
        $data = array();

        // Check If User Has Permission View  All Records
        $Payments = PaymentPurchase::with('purchase.provider')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
        // Multiple Filter
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('provider_id'), function ($query) use ($request) {
                    return $query->whereHas('purchase.provider', function ($q) use ($request) {
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
                            return $query->whereHas('purchase', function ($q) use ($request) {
                                $q->where('Ref', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('purchase.provider', function ($q) use ($request) {
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
            $item['Ref_Purchase'] = $Payment['purchase']->Ref;
            $item['provider_name'] = $Payment['purchase']['provider']->name;
            $item['Reglement'] = $Payment->Reglement;
            $item['montant'] = number_format($Payment->montant, 2, '.', '');

            $data[] = $item;
        }

        $suppliers = provider::where('deleted_at', '=', null)->get(['id', 'name']);
        $purchases = Purchase::get(['Ref', 'id']);

        return response()->json([
            'totalRows' => $totalRows,
            'payments' => $data,
            'purchases' => $purchases,
            'suppliers' => $suppliers,
        ]);

    }

    //----------- Store New Payment Purchase --------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', PaymentPurchase::class);
        
        if($request['montant'] > 0){
            \DB::transaction(function () use ($request) {
                $role = Auth::user()->roles()->first();
                $view_records = Role::findOrFail($role->id)->inRole('record_view');
                $purchase = Purchase::findOrFail($request['purchase_id']);
        
                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === purchase->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $purchase);
                }

                $total_paid = $purchase->paid_amount + $request['montant'];
                $due = $purchase->GrandTotal - $total_paid;

                if ($due === 0.0 || $due < 0.0) {
                    $payment_statut = 'paid';
                } else if ($due !== $purchase->GrandTotal) {
                    $payment_statut = 'partial';
                } else if ($due === $purchase->GrandTotal) {
                    $payment_statut = 'unpaid';
                }

                PaymentPurchase::create([
                    'purchase_id' => $request['purchase_id'],
                    'Ref' => $this->getNumberOrder(),
                    'date' => $request['date'],
                    'Reglement' => $request['Reglement'],
                    'montant' => $request['montant'],
                    'change' => $request['change'],
                    'notes' => $request['notes'],
                    'user_id' => Auth::user()->id,
                ]);

                $purchase->update([
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

    //----------- Update Payment Purchases --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', PaymentPurchase::class);
        
        \DB::transaction(function () use ($id, $request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $payment = PaymentPurchase::findOrFail($id);
    
            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === payment->id
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $purchase = Purchase::whereId($request['purchase_id'])->first();
            $old_total_paid = $purchase->paid_amount - $payment->montant;
            $new_total_paid = $old_total_paid + $request['montant'];

            $due = $purchase->GrandTotal - $new_total_paid;
            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } else if ($due !== $purchase->GrandTotal) {
                $payment_statut = 'partial';
            } else if ($due === $purchase->GrandTotal) {
                $payment_statut = 'unpaid';
            }

            $payment->update([
                'date' => $request['date'],
                'Reglement' => $request['Reglement'],
                'montant' => $request['montant'],
                'change' => $request['change'],
                'notes' => $request['notes'],
            ]);

            $purchase->paid_amount = $new_total_paid;
            $purchase->payment_statut = $payment_statut;
            $purchase->save();

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Update successfully'], 200);
    }

    //----------- Delete Payment Purchase --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', PaymentPurchase::class);
        
        \DB::transaction(function () use ($id, $request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $payment = PaymentPurchase::findOrFail($id);
    
            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === payment->id
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $purchase = Purchase::find($payment->purchase_id);
            $total_paid = $purchase->paid_amount - $payment->montant;
            $due = $purchase->GrandTotal - $total_paid;

            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } else if ($due !== $purchase->GrandTotal) {
                $payment_statut = 'partial';
            } else if ($due === $purchase->GrandTotal) {
                $payment_statut = 'unpaid';
            }

            PaymentPurchase::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            $purchase->update([
                'paid_amount' => $total_paid,
                'payment_statut' => $payment_statut,
            ]);

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Delete successfully'], 200);

    }

    //------------- Send Payment Purchase Return on Email -----------\\

    public function SendEmail(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'view', PaymentPurchase::class);

        $payment = $request->all();
        $pdf = $this->Payment_purchase_pdf($request, $payment['id']);
        $this->Set_config_mail(); // Set_config_mail => BaseController
        $mail = Mail::to($request->to)->send(new Payment_Purchase($payment, $pdf));
        return $mail;
    }

    //----------- Reference order Payment Purchases --------------\\

    public function getNumberOrder()
    {
        $last = DB::table('payment_purchases')->latest('id')->first();

        if ($last) {
            $item = $last->Ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'INV/PR_1111';
        }
        return $code;
    }

    //----------- Payment Purchase PDF --------------\\

    public function Payment_purchase_pdf(Request $request, $id)
    {
        $payment = PaymentPurchase::with('purchase', 'purchase.provider')->findOrFail($id);

        $payment_data['purchase_Ref'] = $payment['purchase']->Ref;
        $payment_data['supplier_name'] = $payment['purchase']['provider']->name;
        $payment_data['supplier_phone'] = $payment['purchase']['provider']->phone;
        $payment_data['supplier_adr'] = $payment['purchase']['provider']->adresse;
        $payment_data['supplier_email'] = $payment['purchase']['provider']->email;
        $payment_data['montant'] = $payment->montant;
        $payment_data['Ref'] = $payment->Ref;
        $payment_data['date'] = $payment->date;
        $payment_data['Reglement'] = $payment->Reglement;

        $helpers = new helpers();
        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->Get_Currency_Code();

        $pdf = \PDF::loadView('pdf.payments_purchase', [
            'symbol' => $symbol,
            'setting' => $settings,
            'payment' => $payment_data,
        ]);

        return $pdf->download('Payment_Purchase.pdf');

    }

    //----------- Export To Excel Payment Purchases  --------------\\

    public function exportExcel(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PaymentPurchase::class);

        return Excel::download(new Payment_Purchase_Export, 'Payment_Purchase.xlsx');
    }

     //-------------------Sms Notifications -----------------\\
     public function Send_SMS(Request $request)
     {
         $payment = PaymentPurchase::with('purchase', 'purchase.provider')->findOrFail($request->id);

         $url = url('/api/Payment_Purchase_PDF/' . $request->id);
         $receiverNumber = $payment['purchase']['provider']->phone;
         $message = "Dear" .' '.$payment['purchase']['provider']->name." \n We are contacting you in regard to a Payment #".$payment['purchase']->Ref.' '.$url.' '. "that has been created on your account. \n We look forward to conducting future business with you.";
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

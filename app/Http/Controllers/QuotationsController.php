<?php

namespace App\Http\Controllers;

use App\Exports\QuotationsExport;
use App\Mail\QuotationMail;
use App\Models\Client;
use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductVariant;
use App\Models\product_warehouse;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Warehouse;
use App\utils\helpers;
use Carbon\Carbon;
use Twilio\Rest\Client as Client_Twilio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use PDF;

class QuotationsController extends BaseController
{

    //---------------- GET ALL QUOTATIONS ---------------\\
    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        // Filter fields With Params to retrieve
        $param = array(
            0 => 'like',
            1 => 'like',
            2 => '=',
            3 => '=',
            4 => '=',
        );
        $columns = array(
            0 => 'Ref',
            1 => 'statut',
            2 => 'client_id',
            3 => 'date',
            4 => 'warehouse_id',
        );
        $data = array();

        // Check If User Has Permission View  All Records
        $Quotations = Quotation::with('client', 'warehouse')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            });

        //Multiple Filter
        $Filtred = $helpers->filter($Quotations, $columns, $param, $request)
        //Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('Ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('statut', 'LIKE', "%{$request->search}%")
                        ->orWhere('GrandTotal', $request->search)
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('client', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('warehouse', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });

        $totalRows = $Filtred->count();
        $Quotations = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($Quotations as $Quotation) {

            $item['id'] = $Quotation->id;
            $item['date'] = $Quotation->date;
            $item['Ref'] = $Quotation->Ref;
            $item['statut'] = $Quotation->statut;
            $item['warehouse_name'] = $Quotation['warehouse']->name;
            $item['client_name'] = $Quotation['client']->name;
            $item['client_email'] = $Quotation['client']->email;
            $item['GrandTotal'] = $Quotation->GrandTotal;

            $data[] = $item;
        }

        $customers = client::where('deleted_at', '=', null)->get();
        $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'totalRows' => $totalRows,
            'quotations' => $data,
            'customers' => $customers,
            'warehouses' => $warehouses,
        ]);
    }

    //------------ Store new Quotation -------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Quotation::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
        ]);

        \DB::transaction(function () use ($request) {

            $order = new Quotation;

            $order->date = $request->date;
            $order->Ref = $this->getNumberOrder();
            $order->statut = $request->statut;
            $order->client_id = $request->client_id;
            $order->GrandTotal = $request->GrandTotal;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->TaxNet = $request->TaxNet;
            $order->discount = $request->Discount;
            $order->shipping = $request->shipping;
            $order->notes = $request->notes;
            $order->user_id = Auth::user()->id;

            $order->save();

            $data = $request['details'];

            foreach ($data as $key => $value) {
                $unit = Unit::where('id', $value['sale_unit_id'])->first();

                $orderDetails[] = [
                    'quotation_id' => $order->id,
                    'quantity' => $value['quantity'],
                    'sale_unit_id' =>  $value['sale_unit_id'],
                    'price' => $value['Unit_price'],
                    'TaxNet' => $value['tax_percent'],
                    'tax_method' => $value['tax_method'],
                    'discount' => $value['discount'],
                    'discount_method' => $value['discount_Method'],
                    'product_id' => $value['product_id'],
                    'product_variant_id' => $value['product_variant_id'],
                    'total' => $value['subtotal'],
                ];
            }
            QuotationDetail::insert($orderDetails);
        }, 10);
        return response()->json(['success' => true]);
    }

    //------------ Update Quotation -------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Quotation::class);

        request()->validate([
            'warehouse_id' => 'required',
            'client_id' => 'required',
        ]);

        \DB::transaction(function () use ($request, $id) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $current_Quotation = Quotation::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === Quotation->id
                $this->authorizeForUser($request->user('api'), 'check_record', $current_Quotation);
            }

            $old_quotation_details = QuotationDetail::where('quotation_id', $id)->get();
            $new_quotation_details = $request['details'];
            $length = sizeof($new_quotation_details);

            // Get Ids details
            $new_details_id = [];
            foreach ($new_quotation_details as $new_detail) {
                $new_details_id[] = $new_detail['id'];
            }

            // Init quotation with old Parametre
            $old_detail_id = [];
            foreach ($old_quotation_details as $key => $value) {
                $old_detail_id[] = $value->id;

                // Delete Detail
                if (!in_array($old_detail_id[$key], $new_details_id)) {
                    $QuotationDetail = QuotationDetail::findOrFail($value->id);
                    $QuotationDetail->delete();
                }

            }

            // Update quotation with New request
            foreach ($new_quotation_details as $key => $product_detail) {

                $QuoteDetail['quotation_id'] = $id;
                $QuoteDetail['quantity'] = $product_detail['quantity'];
                $QuoteDetail['sale_unit_id'] = $product_detail['sale_unit_id'];
                $QuoteDetail['product_id'] = $product_detail['product_id'];
                $QuoteDetail['product_variant_id'] = $product_detail['product_variant_id'];
                $QuoteDetail['price'] = $product_detail['Unit_price'];
                $QuoteDetail['TaxNet'] = $product_detail['tax_percent'];
                $QuoteDetail['tax_method'] = $product_detail['tax_method'];
                $QuoteDetail['discount'] = $product_detail['discount'];
                $QuoteDetail['discount_method'] = $product_detail['discount_Method'];

                $QuoteDetail['total'] = $product_detail['subtotal'];

                if (!in_array($product_detail['id'], $old_detail_id)) {
                    QuotationDetail::Create($QuoteDetail);
                } else {
                    QuotationDetail::where('id', $product_detail['id'])->update($QuoteDetail);
                }
            }

            $current_Quotation->update([
                'client_id' => $request['client_id'],
                'warehouse_id' => $request['warehouse_id'],
                'statut' => $request['statut'],
                'notes' => $request['notes'],
                'tax_rate' => $request['tax_rate'],
                'TaxNet' => $request['TaxNet'],
                'date' => $request['date'],
                'discount' => $request['discount'],
                'shipping' => $request['shipping'],
                'GrandTotal' => $request['GrandTotal'],
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ Delete Quotation -------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Quotation::class);

        \DB::transaction(function () use ($id, $request) {

            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $Quotation = Quotation::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === Quotation->id
                $this->authorizeForUser($request->user('api'), 'check_record', $Quotation);
            }
            $Quotation->details()->delete();
            $Quotation->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Quotation::class);

        \DB::transaction(function () use ($request) {

            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $selectedIds = $request->selectedIds;
            foreach ($selectedIds as $Quotation_id) {
                $Quotation = Quotation::findOrFail($Quotation_id);

                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === Quotation->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $Quotation);
                }
                $Quotation->details()->delete();
                $Quotation->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------- Send Quotation on Email -----------\\

    public function SendEmail(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);

        $quote = $request->all();
        $pdf = $this->Quotation_pdf($request, $quote['id']);
        $this->Set_config_mail(); // Set_config_mail => BaseController
        $mail = Mail::to($request->to)->send(new QuotationMail($quote, $pdf));
        return $mail;
    }

    //------------ Export Excel Quotations -------------\\

    public function exportExcel(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);
        return Excel::download(new QuotationsExport, 'Quotations.xlsx');
    }

    //---------------- Get Details Quotation-----------------\\

    public function show(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $quotation_data = Quotation::with('details.product.unitSale')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);

        $details = array();

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === Quotation->id
            $this->authorizeForUser($request->user('api'), 'check_record', $quotation_data);
        }

        $quote['Ref'] = $quotation_data->Ref;
        $quote['date'] = $quotation_data->date;
        $quote['note'] = $quotation_data->notes;
        $quote['statut'] = $quotation_data->statut;
        $quote['discount'] = $quotation_data->discount;
        $quote['shipping'] = $quotation_data->shipping;
        $quote['tax_rate'] = $quotation_data->tax_rate;
        $quote['TaxNet'] = $quotation_data->TaxNet;
        $quote['client_name'] = $quotation_data['client']->name;
        $quote['client_phone'] = $quotation_data['client']->phone;
        $quote['client_adr'] = $quotation_data['client']->adresse;
        $quote['client_email'] = $quotation_data['client']->email;
        $quote['warehouse'] = $quotation_data['warehouse']->name;
        $quote['GrandTotal'] = number_format($quotation_data['GrandTotal'], 2, '.', '');

        foreach ($quotation_data['details'] as $detail) {

             //check if detail has sale_unit_id Or Null
             if($detail->sale_unit_id !== null){
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
            }else{
                $product_unit_sale_id = Product::with('unitSale')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_sale_id['unitSale']->id)->first();
            }

            if ($detail->product_variant_id) {

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $data['code'] = $productsVariants->name . '-' . $detail['product']['code'];

            } else {
                $data['code'] = $detail['product']['code'];
            }
            
            $data['quantity'] = $detail->quantity;
            $data['total'] = $detail->total;
            $data['name'] = $detail['product']['name'];
            $data['price'] = $detail->price;
            $data['unit_sale'] = $unit->ShortName;

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = $detail->discount;
            } else {
                $data['DiscountNet'] = $detail->price * $detail->discount / 100;
            }

            $tax_price = $detail->TaxNet * (($detail->price - $data['DiscountNet']) / 100);
            $data['Unit_price'] = $detail->price;
            $data['discount'] = $detail->discount;

            if ($detail->tax_method == '1') {
                $data['Net_price'] = $detail->price - $data['DiscountNet'];
                $data['taxe'] = $tax_price;
            } else {
                $data['Net_price'] = ($detail->price - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = $detail->price - $data['Net_price'] - $data['DiscountNet'];
            }

            $details[] = $data;
        }

        $company = Setting::where('deleted_at', '=', null)->first();

        return response()->json([
            'quote' => $quote,
            'details' => $details,
            'company' => $company,
        ]);

    }

    //---------------- Reference Number Of Quotation  ---------------\\

    public function getNumberOrder()
    {
        $last = DB::table('quotations')->latest('id')->first();

        if ($last) {
            $item = $last->Ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'QT_1111';
        }
        return $code;

    }

    //---------------- Quotation PDF ---------------\\

    public function Quotation_pdf(Request $request, $id)
    {

        $details = array();
        $helpers = new helpers();
        $Quotation = Quotation::with('details.product.unitSale')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);

        $quote['client_name'] = $Quotation['client']->name;
        $quote['client_phone'] = $Quotation['client']->phone;
        $quote['client_adr'] = $Quotation['client']->adresse;
        $quote['client_email'] = $Quotation['client']->email;
        $quote['TaxNet'] = number_format($Quotation->TaxNet, 2, '.', '');
        $quote['discount'] = number_format($Quotation->discount, 2, '.', '');
        $quote['shipping'] = number_format($Quotation->shipping, 2, '.', '');
        $quote['statut'] = $Quotation->statut;
        $quote['Ref'] = $Quotation->Ref;
        $quote['date'] = $Quotation->date;
        $quote['GrandTotal'] = number_format($Quotation->GrandTotal, 2, '.', '');

        $detail_id = 0;
        foreach ($Quotation['details'] as $detail) {

            //check if detail has sale_unit_id Or Null
             if($detail->sale_unit_id !== null){
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
            }else{
                $product_unit_sale_id = Product::with('unitSale')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_sale_id['unitSale']->id)->first();
            }

            if ($detail->product_variant_id) {

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $data['code'] = $productsVariants->name . '-' . $detail['product']['code'];
            } else {
                $data['code'] = $detail['product']['code'];
            }

                $data['detail_id'] = $detail_id += 1;
                $data['quantity'] = number_format($detail->quantity, 2, '.', '');
                $data['total'] = number_format($detail->total, 2, '.', '');
                $data['name'] = $detail['product']['name'];
                $data['unitSale'] = $unit->ShortName;
                $data['price'] = number_format($detail->price, 2, '.', '');

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = number_format($detail->discount, 2, '.', '');
            } else {
                $data['DiscountNet'] = number_format($detail->price * $detail->discount / 100, 2, '.', '');
            }

            $tax_price = $detail->TaxNet * (($detail->price - $data['DiscountNet']) / 100);
            $data['Unit_price'] = number_format($detail->price, 2, '.', '');
            $data['discount'] = number_format($detail->discount, 2, '.', '');

            if ($detail->tax_method == '1') {
                $data['Net_price'] = $detail->price - $data['DiscountNet'];
                $data['taxe'] = number_format($tax_price, 2, '.', '');
            } else {
                $data['Net_price'] = ($detail->price - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = number_format($detail->price - $data['Net_price'] - $data['DiscountNet'], 2, '.', '');
            }

            $details[] = $data;
        }

        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->Get_Currency_Code();

        $pdf = \PDF::loadView('pdf.quotation_pdf', [
            'symbol' => $symbol,
            'setting' => $settings,
            'quote' => $quote,
            'details' => $details,
        ]);
        return $pdf->download('Quotation.pdf');

    }

    //---------------- Show Form Create Quotation ---------------\\

    public function create(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Quotation::class);

        $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'clients' => $clients,
            'warehouses' => $warehouses,
        ]);
    }

    //------------- Show Form Edit Quotation -----------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Quotation::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $Quotation = Quotation::with('details.product.unitSale')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);
        $details = array();
        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === Quotation->id
            $this->authorizeForUser($request->user('api'), 'check_record', $Quotation);
        }

        if ($Quotation->client_id) {
            if (Client::where('id', $Quotation->client_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $quote['client_id'] = $Quotation->client_id;
            } else {
                $quote['client_id'] = '';
            }
        } else {
            $quote['client_id'] = '';
        }

        if ($Quotation->warehouse_id) {
            if (Warehouse::where('id', $Quotation->warehouse_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $quote['warehouse_id'] = $Quotation->warehouse_id;
            } else {
                $quote['warehouse_id'] = '';
            }
        } else {
            $quote['warehouse_id'] = '';
        }

        $quote['date'] = $Quotation->date;
        $quote['tax_rate'] = $Quotation->tax_rate;
        $quote['discount'] = $Quotation->discount;
        $quote['shipping'] = $Quotation->shipping;
        $quote['statut'] = $Quotation->statut;
        $quote['notes'] = $Quotation->notes;

        $detail_id = 0;
        foreach ($Quotation['details'] as $detail) {

             //check if detail has sale_unit_id Or Null
             if($detail->sale_unit_id !== null){
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
            }else{
                $product_unit_sale_id = Product::with('unitSale')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_sale_id['unitSale']->id)->first();
                $data['no_unit'] = 0;
            }

            if ($detail->product_variant_id) {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('warehouse_id', $Quotation->warehouse_id)
                    ->where('deleted_at', '=', null)
                    ->first();

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['product_variant_id'] = $detail->product_variant_id;
                $data['code'] = $productsVariants->name . '-' . $detail['product']['code'];

                if ($unit && $unit->operator == '/') {
                    $data['stock'] = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $data['stock'] = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $data['stock'] = 0;
                }

            } else {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('deleted_at', '=', null)
                    ->where('warehouse_id', $Quotation->warehouse_id)
                    ->where('product_variant_id', '=', null)
                    ->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['product_variant_id'] = null;
                $data['code'] = $detail['product']['code'];

                if ($unit && $unit->operator == '/') {
                    $data['stock'] = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $data['stock'] = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $data['stock'] = 0;
                }

            }

            $data['id'] = $detail->id;
            $data['detail_id'] = $detail_id += 1;
            $data['product_id'] = $detail->product_id;
            $data['quantity'] = $detail->quantity;
            $data['name'] = $detail['product']['name'];
            $data['etat'] = 'current';
            $data['qte_copy'] = $detail->quantity;
            $data['total'] = $detail->total;
            $data['unitSale'] = $unit->ShortName;
            $data['sale_unit_id'] = $unit->id;

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = $detail->discount;
            } else {
                $data['DiscountNet'] = $detail->price * $detail->discount / 100;
            }

            $tax_price = $detail->TaxNet * (($detail->price - $data['DiscountNet']) / 100);
            $data['Unit_price'] = $detail->price;
            $data['tax_percent'] = $detail->TaxNet;
            $data['tax_method'] = $detail->tax_method;
            $data['discount'] = $detail->discount;
            $data['discount_Method'] = $detail->discount_method;

            if ($detail->tax_method == '1') {
                $data['Net_price'] = $detail->price - $data['DiscountNet'];
                $data['taxe'] = $tax_price;
                $data['subtotal'] = ($data['Net_price'] * $data['quantity']) + ($tax_price * $data['quantity']);
            } else {
                $data['Net_price'] = ($detail->price - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = $detail->price - $data['Net_price'] - $data['DiscountNet'];
                $data['subtotal'] = ($data['Net_price'] * $data['quantity']) + ($tax_price * $data['quantity']);
            }

            $details[] = $data;
        }

        $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'details' => $details,
            'quote' => $quote,
            'clients' => $clients,
            'warehouses' => $warehouses,
        ]);
    }

     //-------------------Sms Notifications -----------------\\

     public function Send_SMS(Request $request)
     {
         $Quotation = Quotation::where('deleted_at', '=', null)->findOrFail($request->id);
         $url = url('/api/Quote_PDF/' . $request->id);
         $receiverNumber = $Quotation['client']->phone;
         $message = "Dear" .' '.$Quotation['client']->name." \n We are contacting you in regard to a Quotation #".$Quotation->Ref.' '.$url.' '. "that has been created on your account. \n We look forward to conducting future business with you.";
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

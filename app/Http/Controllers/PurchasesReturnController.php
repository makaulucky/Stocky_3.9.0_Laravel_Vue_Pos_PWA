<?php

namespace App\Http\Controllers;

use App\Exports\Purchase_Return;
use App\Mail\ReturnMail;
use App\Models\Unit;
use App\Models\PaymentPurchaseReturns;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\product_warehouse;
use App\Models\Provider;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetails;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Warehouse;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Twilio\Rest\Client as Client_Twilio;
use DB;
use PDF;

class PurchasesReturnController extends BaseController
{

    //------------ GET ALL Purchases Return  --------------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PurchaseReturn::class);
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
            3 => 'like',
            4 => '=',
            5 => '=',
        );
        $columns = array(
            0 => 'Ref',
            1 => 'statut',
            2 => 'provider_id',
            3 => 'payment_statut',
            4 => 'warehouse_id',
            5 => 'date',
        );
        $data = array();

        // Check If User Has Permission View  All Records
        $PurchaseReturn = PurchaseReturn::with('facture', 'provider', 'warehouse')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            });

        //Multiple Filter
        $Filtred = $helpers->filter($PurchaseReturn, $columns, $param, $request)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('Ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('statut', 'LIKE', "%{$request->search}%")
                        ->orWhere('GrandTotal', $request->search)
                        ->orWhere('payment_statut', 'like', "$request->search")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('provider', function ($q) use ($request) {
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
        $PurchaseReturn = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($PurchaseReturn as $Purchase_Return) {

            $item['id'] = $Purchase_Return->id;
            $item['date'] = $Purchase_Return->date;
            $item['Ref'] = $Purchase_Return->Ref;
            $item['discount'] = $Purchase_Return->discount;
            $item['shipping'] = $Purchase_Return->shipping;
            $item['statut'] = $Purchase_Return->statut;
            $item['warehouse_name'] = $Purchase_Return['warehouse']->name;
            $item['provider_id'] = $Purchase_Return['provider']->id;
            $item['provider_name'] = $Purchase_Return['provider']->name;
            $item['provider_email'] = $Purchase_Return['provider']->email;
            $item['provider_tele'] = $Purchase_Return['provider']->phone;
            $item['provider_code'] = $Purchase_Return['provider']->code;
            $item['provider_adr'] = $Purchase_Return['provider']->adresse;
            $item['GrandTotal'] = number_format($Purchase_Return['GrandTotal'], 2, '.', '');
            $item['paid_amount'] = number_format($Purchase_Return['paid_amount'], 2, '.', '');
            $item['due'] = number_format($item['GrandTotal'] - $item['paid_amount'], 2, '.', '');
            $item['payment_status'] = $Purchase_Return['payment_statut'];

            $data[] = $item;
        }

        $suppliers = provider::where('deleted_at', '=', null)->get(['id', 'name']);
        $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'totalRows' => $totalRows,
            'purchase_returns' => $data,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);

    }

    //------------ Store New Purchase Return  --------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', PurchaseReturn::class);

        request()->validate([
            'supplier_id' => 'required',
            'warehouse_id' => 'required',
        ]);

        \DB::transaction(function () use ($request) {
            $order = new PurchaseReturn;

            $order->date = $request->date;
            $order->Ref = $this->getNumberOrder();
            $order->provider_id = $request->supplier_id;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->TaxNet = $request->TaxNet;
            $order->discount = $request->discount;
            $order->shipping = $request->shipping;
            $order->statut = $request->statut;
            $order->GrandTotal = $request->GrandTotal;
            $order->payment_statut = 'unpaid';
            $order->notes = $request->notes;
            $order->user_id = Auth::user()->id;

            $order->save();

            $data = $request['details'];
            foreach ($data as $key => $value) {
                $unit = Unit::where('id', $value['purchase_unit_id'])->first();
                $orderDetails[] = [
                    'purchase_return_id' => $order->id,
                    'quantity' => $value['quantity'],
                    'cost' => $value['Unit_cost'],
                    'purchase_unit_id' =>  $value['purchase_unit_id'],
                    'TaxNet' => $value['tax_percent'],
                    'tax_method' => $value['tax_method'],
                    'discount' => $value['discount'],
                    'discount_method' => $value['discount_Method'],
                    'product_id' => $value['product_id'],
                    'product_variant_id' => $value['product_variant_id'],
                    'total' => $value['subtotal'],
                ];

            
                if ($order->statut == "completed") {
                    if ($value['product_variant_id'] !== null) {

                        $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->where('product_variant_id', $value['product_variant_id'])
                            ->first();

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
                            }

                            $product_warehouse->save();
                        }

                    } else {
                        $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->first();

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
                            }

                            $product_warehouse->save();
                        }
                    }
                }

            }
            PurchaseReturnDetails::insert($orderDetails);
        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ Update Purchase Return --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', PurchaseReturn::class);

        request()->validate([
            'warehouse_id' => 'required',
            'supplier_id' => 'required',
        ]);

        \DB::transaction(function () use ($request, $id) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $current_PurchaseReturn = PurchaseReturn::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === PurchaseReturn->id
                $this->authorizeForUser($request->user('api'), 'check_record', $current_PurchaseReturn);
            }

            $old_Return_Details = PurchaseReturnDetails::where('purchase_return_id', $id)->get();
            $New_Return_Details = $request['details'];
            $length = sizeof($New_Return_Details);

            // Get Ids details
            $new_products_id = [];
            foreach ($New_Return_Details as $new_detail) {
                $new_products_id[] = $new_detail['id'];
            }

            // Init Data with old Parametre
            $old_products_id = [];
            foreach ($old_Return_Details as $key => $value) {
                $old_products_id[] = $value->id;

                 //check if detail has purchase_unit_id Or Null
                 if($value['purchase_unit_id'] !== null){
                    $unit = Unit::where('id', $value['purchase_unit_id'])->first();
                }else{
                    $product_unit_purchase_id = Product::with('unitPurchase')
                    ->where('id', $value['product_id'])
                    ->first();
                    $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
                }

                if($value['purchase_unit_id'] !== null){
                    if ($current_PurchaseReturn->statut == "completed") {
                        if ($value['product_variant_id'] !== null) {

                            $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $current_PurchaseReturn->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->where('product_variant_id', $value['product_variant_id'])
                                ->first();

                            if ($unit && $product_warehouse) {
                                if ($unit->operator == '/') {
                                    $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                                } else {
                                    $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                                }
                                $product_warehouse->save();
                            }

                        } else {
                            $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $current_PurchaseReturn->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->first();

                            if ($unit && $product_warehouse) {
                                if ($unit->operator == '/') {
                                    $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                                } else {
                                    $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                                }
                                $product_warehouse->save();
                            }
                        }
                    }

                    // Delete Detail
                    if (!in_array($old_products_id[$key], $new_products_id)) {
                        $PurchaseReturnDetails = PurchaseReturnDetails::findOrFail($value->id);
                        $PurchaseReturnDetails->delete();
                    }
                }

            }

            // Update Data with New request
            foreach ($New_Return_Details as $key => $product_detail) {

                if($product_detail['no_unit'] !== 0){
                    $unit_prod = Unit::where('id', $product_detail['purchase_unit_id'])->first();

                    if ($request->statut == "completed") {
                        if ($product_detail['product_variant_id'] !== null) {
                            $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $request->warehouse_id)
                                ->where('product_id', $product_detail['product_id'])
                                ->where('product_variant_id', $product_detail['product_variant_id'])
                                ->first();

                            if ($unit_prod && $product_warehouse) {
                                if ($unit_prod->operator == '/') {
                                    $product_warehouse->qte -= $product_detail['quantity'] / $unit_prod->operator_value;
                                } else {
                                    $product_warehouse->qte -= $product_detail['quantity'] * $unit_prod->operator_value;
                                }
                                $product_warehouse->save();
                            }

                        } else {
                            $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $request->warehouse_id)
                                ->where('product_id', $product_detail['product_id'])
                                ->first();

                            if ($unit_prod && $product_warehouse) {
                                if ($unit_prod->operator == '/') {
                                    $product_warehouse->qte -= $product_detail['quantity'] / $unit_prod->operator_value;
                                } else {
                                    $product_warehouse->qte -= $product_detail['quantity'] * $unit_prod->operator_value;
                                }
                                $product_warehouse->save();
                            }
                        }
                    }

                    $orderDetails['purchase_return_id'] = $id;
                    $orderDetails['cost'] = $product_detail['Unit_cost'];
                    $orderDetails['purchase_unit_id'] = $product_detail['purchase_unit_id'];
                    $orderDetails['TaxNet'] = $product_detail['tax_percent'];
                    $orderDetails['tax_method'] = $product_detail['tax_method'];
                    $orderDetails['discount'] = $product_detail['discount'];
                    $orderDetails['discount_method'] = $product_detail['discount_Method'];
                    $orderDetails['quantity'] = $product_detail['quantity'];
                    $orderDetails['product_id'] = $product_detail['product_id'];
                    $orderDetails['product_variant_id'] = $product_detail['product_variant_id'];
                    $orderDetails['total'] = $product_detail['subtotal'];

                    if (!in_array($product_detail['id'], $old_products_id)) {
                        PurchaseReturnDetails::Create($orderDetails);
                    } else {
                        PurchaseReturnDetails::where('id', $product_detail['id'])->update($orderDetails);
                    }
                }

            }

            $due = $request['GrandTotal'] - $current_PurchaseReturn->paid_amount;
            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } else if ($due != $request['GrandTotal']) {
                $payment_statut = 'partial';
            } else if ($due == $request['GrandTotal']) {
                $payment_statut = 'unpaid';
            }

            $current_PurchaseReturn->update([
                'date' => $request['date'],
                'provider_id' => $request['supplier_id'],
                'warehouse_id' => $request['warehouse_id'],
                'notes' => $request['notes'],
                'statut' => $request['statut'],
                'tax_rate' => $request['tax_rate'],
                'TaxNet' => $request['TaxNet'],
                'discount' => $request['discount'],
                'shipping' => $request['shipping'],
                'GrandTotal' => $request['GrandTotal'],
                'payment_statut' => $payment_statut,
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ Delete Purchase Return  --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', PurchaseReturn::class);

        \DB::transaction(function () use ($id, $request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $current_PurchaseReturn = PurchaseReturn::findOrFail($id);
            $old_Return_Details = PurchaseReturnDetails::where('purchase_return_id', $id)->get();

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === PurchaseReturn->id
                $this->authorizeForUser($request->user('api'), 'check_record', $current_PurchaseReturn);
            }

            foreach ($old_Return_Details as $key => $value) {

                 //check if detail has purchase_unit_id Or Null
                 if($value['purchase_unit_id'] !== null){
                    $unit = Unit::where('id', $value['purchase_unit_id'])->first();
                }else{
                    $product_unit_purchase_id = Product::with('unitPurchase')
                    ->where('id', $value['product_id'])
                    ->first();
                    $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
                }

                if ($current_PurchaseReturn->statut == "completed") {
                    if ($value['product_variant_id'] !== null) {

                        $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $current_PurchaseReturn->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->where('product_variant_id', $value['product_variant_id'])
                            ->first();

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }

                    } else {
                        $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $current_PurchaseReturn->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->first();

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }
                    }
                }
                
            }
            $current_PurchaseReturn->details()->delete();
            $current_PurchaseReturn->update([
                'deleted_at' => Carbon::now(),
            ]);

            PaymentPurchaseReturns::where('purchase_return_id', $id)->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'delete', PurchaseReturn::class);

        \DB::transaction(function () use ($request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $selectedIds = $request->selectedIds;

            foreach ($selectedIds as $PurchaseReturn_id) {
                $current_PurchaseReturn = PurchaseReturn::findOrFail($PurchaseReturn_id);
                $old_Return_Details = PurchaseReturnDetails::where('purchase_return_id', $PurchaseReturn_id)->get();


                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === current_PurchaseReturn->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $current_PurchaseReturn);
                }
                foreach ($old_Return_Details as $key => $value) {

                    //check if detail has purchase_unit_id Or Null
                    if($value['purchase_unit_id'] !== null){
                       $unit = Unit::where('id', $value['purchase_unit_id'])->first();
                   }else{
                       $product_unit_purchase_id = Product::with('unitPurchase')
                       ->where('id', $value['product_id'])
                       ->first();
                       $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
                   }
   
                   if ($current_PurchaseReturn->statut == "completed") {
                       if ($value['product_variant_id'] !== null) {
   
                           $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                               ->where('warehouse_id', $current_PurchaseReturn->warehouse_id)
                               ->where('product_id', $value['product_id'])
                               ->where('product_variant_id', $value['product_variant_id'])
                               ->first();
   
                           if ($unit && $product_warehouse) {
                               if ($unit->operator == '/') {
                                   $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                               } else {
                                   $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                               }
                               $product_warehouse->save();
                           }
   
                       } else {
                           $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                               ->where('warehouse_id', $current_PurchaseReturn->warehouse_id)
                               ->where('product_id', $value['product_id'])
                               ->first();
   
                           if ($unit && $product_warehouse) {
                               if ($unit->operator == '/') {
                                   $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                               } else {
                                   $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                               }
                               $product_warehouse->save();
                           }
                       }
                   }
                   
               }
               $current_PurchaseReturn->details()->delete();
               $current_PurchaseReturn->update([
                   'deleted_at' => Carbon::now(),
               ]);

                PaymentPurchaseReturns::where('purchase_return_id', $PurchaseReturn_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);

    }

    //---------------- Show Form Create Purchase Return ---------------\\

    public function create(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', PurchaseReturn::class);

        $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        $suppliers = Provider::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);

    }

    //------------- Send Purchase Return on Email -----------\\

    public function Send_Email(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'view', PurchaseReturn::class);

        $Purchase_Return = $request->all();
        $pdf = $this->Return_pdf($request, $Purchase_Return['id']);
        $this->Set_config_mail(); // Set_config_mail => BaseController
        $mail = Mail::to($request->to)->send(new ReturnMail($Purchase_Return, $pdf));
        return $mail;
    }

    //------------ Export Purchase Return EXCEL  --------------\\

    public function exportExcel(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PurchaseReturn::class);
        return Excel::download(new Purchase_Return, 'Purchases_Return.xlsx');
    }

    //------------- GET Payments Purchase Return BY ID-----------\\

    public function Payment_Returns(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'view', PaymentPurchaseReturns::class);

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $PurchaseReturn = PurchaseReturn::findOrFail($id);

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === PurchaseReturn->id
            $this->authorizeForUser($request->user('api'), 'check_record', $PurchaseReturn);
        }

        $payments = PaymentPurchaseReturns::with('PurchaseReturn')
            ->where('purchase_return_id', $id)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })->orderBy('id', 'DESC')->get();

        $due = $PurchaseReturn->GrandTotal - $PurchaseReturn->paid_amount;

        return response()->json(['payments' => $payments, 'due' => $due]);
    }

    //------------ Reference Number Purchase Return --------------\\

    public function getNumberOrder()
    {
        $last = DB::table('purchase_returns')->latest('id')->first();

        if ($last) {
            $item = $last->Ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'RT_1111';
        }
        return $code;

    }

    //---------------- Get Details Purchase Return  -----------------\\

    public function show(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'view', PurchaseReturn::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $Purchase_Return = PurchaseReturn::with('details.product.unitPurchase')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);

        $details = array();

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === PurchaseReturn->id
            $this->authorizeForUser($request->user('api'), 'check_record', $Purchase_Return);
        }

        $return_details['Ref'] = $Purchase_Return->Ref;
        $return_details['date'] = $Purchase_Return->date;
        $return_details['statut'] = $Purchase_Return->statut;
        $return_details['note'] = $Purchase_Return->notes;
        $return_details['discount'] = $Purchase_Return->discount;
        $return_details['shipping'] = $Purchase_Return->shipping;
        $return_details['tax_rate'] = $Purchase_Return->tax_rate;
        $return_details['TaxNet'] = $Purchase_Return->TaxNet;
        $return_details['supplier_name'] = $Purchase_Return['provider']->name;
        $return_details['supplier_email'] = $Purchase_Return['provider']->email;
        $return_details['supplier_phone'] = $Purchase_Return['provider']->phone;
        $return_details['supplier_adr'] = $Purchase_Return['provider']->adresse;
        $return_details['warehouse'] = $Purchase_Return['warehouse']->name;
        $return_details['GrandTotal'] = number_format($Purchase_Return->GrandTotal, 2, '.', '');
        $return_details['paid_amount'] = number_format($Purchase_Return->paid_amount, 2, '.', '');
        $return_details['due'] = number_format($return_details['GrandTotal'] - $return_details['paid_amount'] , 2, '.', '');
        $return_details['payment_status'] = $Purchase_Return->payment_statut;

        foreach ($Purchase_Return['details'] as $detail) {

             //-------check if detail has purchase_unit_id Or Null
             if($detail->purchase_unit_id !== null){
                $unit = Unit::where('id', $detail->purchase_unit_id)->first();
            }else{
                $product_unit_purchase_id = Product::with('unitPurchase')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
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
            $data['cost'] = $detail->cost;
            $data['unit_purchase'] = $unit->ShortName;

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = $detail->discount;
            } else {
                $data['DiscountNet'] = $detail->cost * $detail->discount / 100;
            }
            $tax_cost = $detail->TaxNet * (($detail->cost - $data['DiscountNet']) / 100);
            $data['Unit_cost'] = $detail->cost;
            $data['discount'] = $detail->discount;
            if ($detail->tax_method == '1') {
                $data['Net_cost'] = $detail->cost - $data['DiscountNet'];
                $data['taxe'] = $tax_cost;
            } else {
                $data['Net_cost'] = ($detail->cost - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = $detail->cost - $data['Net_cost'] - $data['DiscountNet'];
            }
            $details[] = $data;
        }

        $company = Setting::where('deleted_at', '=', null)->first();

        return response()->json([
            'details' => $details,
            'purchase_return' => $return_details,
            'company' => $company,
        ]);

    }

    //------------- Purchase Return PDF-----------\\

    public function Return_pdf(Request $request, $id)
    {

        $details = array();
        $helpers = new helpers();
        $PurchaseReturn = PurchaseReturn::with('details.product.unitPurchase')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);

        $return_details['supplier_name'] = $PurchaseReturn['provider']->name;
        $return_details['supplier_phone'] = $PurchaseReturn['provider']->phone;
        $return_details['supplier_adr'] = $PurchaseReturn['provider']->adresse;
        $return_details['supplier_email'] = $PurchaseReturn['provider']->email;
        $return_details['TaxNet'] = number_format($PurchaseReturn->TaxNet, 2, '.', '');
        $return_details['discount'] = number_format($PurchaseReturn->discount, 2, '.', '');
        $return_details['shipping'] = number_format($PurchaseReturn->shipping, 2, '.', '');
        $return_details['statut'] = $PurchaseReturn->statut;
        $return_details['Ref'] = $PurchaseReturn->Ref;
        $return_details['date'] = $PurchaseReturn->date;
        $return_details['GrandTotal'] = number_format($PurchaseReturn->GrandTotal, 2, '.', '');
        $return_details['paid_amount'] = number_format($PurchaseReturn->paid_amount, 2, '.', '');
        $return_details['due'] = number_format($return_details['GrandTotal'] - $return_details['paid_amount'], 2, '.', '');
        $return_details['payment_status'] = $PurchaseReturn->payment_statut;

        $detail_id = 0;
        foreach ($PurchaseReturn['details'] as $detail) {

            //-------check if detail has purchase_unit_id Or Null
            if($detail->purchase_unit_id !== null){
                $unit = Unit::where('id', $detail->purchase_unit_id)->first();
            }else{
                $product_unit_purchase_id = Product::with('unitPurchase')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
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
                $data['cost'] = number_format($detail->cost, 2, '.', '');
                $data['unit_purchase'] = $unit->ShortName;

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = number_format($detail->discount, 2, '.', '');
            } else {
                $data['DiscountNet'] = number_format($detail->cost * $detail->discount / 100, 2, '.', '');
            }

            $tax_cost = $detail->TaxNet * (($detail->cost - $data['DiscountNet']) / 100);
            $data['Unit_cost'] = number_format($detail->cost, 2, '.', '');
            $data['discount'] = number_format($detail->discount, 2, '.', '');

            if ($detail->tax_method == '1') {

                $data['Net_cost'] = $detail->cost - $data['DiscountNet'];
                $data['taxe'] = number_format($tax_cost, 2, '.', '');
            } else {
                $data['Net_cost'] = ($detail->cost - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = number_format($detail->cost - $data['Net_cost'] - $data['DiscountNet'], 2, '.', '');
            }

            $details[] = $data;
        }

        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->Get_Currency_Code();

        $pdf = \PDF::loadView('pdf.Purchase_Return_pdf', [
            'symbol' => $symbol,
            'setting' => $settings,
            'return_purchase' => $return_details,
            'details' => $details,
        ]);

        return $pdf->download('Purchase_Return.pdf');
    }

    //------------- Show Form Edit Purchase Return-----------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', PurchaseReturn::class);

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $Purchase_Return = PurchaseReturn::with('details.product.unitPurchase')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);
        $details = array();
        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === PurchaseReturn->id
            $this->authorizeForUser($request->user('api'), 'check_record', $Purchase_Return);
        }

        if ($Purchase_Return->provider_id) {
            if (Provider::where('id', $Purchase_Return->provider_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $Return_detail['supplier_id'] = $Purchase_Return->provider_id;
            } else {
                $Return_detail['supplier_id'] = '';
            }
        } else {
            $Return_detail['supplier_id'] = '';
        }

        if ($Purchase_Return->warehouse_id) {
            if (Warehouse::where('id', $Purchase_Return->warehouse_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $Return_detail['warehouse_id'] = $Purchase_Return->warehouse_id;
            } else {
                $Return_detail['warehouse_id'] = '';
            }
        } else {
            $Return_detail['warehouse_id'] = '';
        }

        $Return_detail['date'] = $Purchase_Return->date;
        $Return_detail['tax_rate'] = $Purchase_Return->tax_rate;
        $Return_detail['TaxNet'] = $Purchase_Return->TaxNet;
        $Return_detail['discount'] = $Purchase_Return->discount;
        $Return_detail['shipping'] = $Purchase_Return->shipping;
        $Return_detail['notes'] = $Purchase_Return->notes;
        $Return_detail['statut'] = $Purchase_Return->statut;

        $detail_id = 0;
        foreach ($Purchase_Return['details'] as $detail) {

            //-------check if detail has purchase_unit_id Or Null
            if($detail->purchase_unit_id !== null){
                $unit = Unit::where('id', $detail->purchase_unit_id)->first();
                $data['no_unit'] = 1;
            }else{
                $product_unit_purchase_id = Product::with('unitPurchase')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
                $data['no_unit'] = 0;
            }

            if ($detail->product_variant_id) {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('deleted_at', '=', null)
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('warehouse_id', $Purchase_Return->warehouse_id)
                    ->first();

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['code'] = $productsVariants->name . '-' . $detail['product']['code'];
                $data['product_variant_id'] = $detail->product_variant_id;

                if ($unit && $unit->operator == '/') {
                    $data['stock'] = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $data['stock'] = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $data['stock'] = 0;
                }

            } else {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('warehouse_id', $Purchase_Return->warehouse_id)
                    ->where('deleted_at', '=', null)->where('product_variant_id', '=', null)
                    ->first();

                    $item_product ? $data['del'] = 0 : $data['del'] = 1;
                    $data['code'] = $detail['product']['code'];
                    $data['product_variant_id'] = null;

                if ($unit && $unit->operator == '/') {
                    $data['stock'] = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $data['stock'] = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $data['stock'] = 0;
                }

            }

            $data['name'] = $detail['product']['name'];
            $data['id'] = $detail->id;
            $data['detail_id'] = $detail_id += 1;
            $data['quantity'] = $detail->quantity;
            $data['product_id'] = $detail->product_id;
            $data['unitPurchase'] = $unit->ShortName;
            $data['purchase_unit_id'] = $unit->id;


            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = $detail->discount;
            } else {
                $data['DiscountNet'] = $detail->cost * $detail->discount / 100;
            }

            $tax_cost = $detail->TaxNet * (($detail->cost - $data['DiscountNet']) / 100);
            $data['Unit_cost'] = $detail->cost;
            $data['tax_percent'] = $detail->TaxNet;
            $data['tax_method'] = $detail->tax_method;
            $data['discount'] = $detail->discount;
            $data['discount_Method'] = $detail->discount_method;

            if ($detail->tax_method == '1') {
                $data['Net_cost'] = $detail->cost - $data['DiscountNet'];
                $data['taxe'] = $tax_cost;
                $data['subtotal'] = ($data['Net_cost'] * $data['quantity']) + ($tax_cost * $data['quantity']);
            } else {
                $data['Net_cost'] = ($detail->cost - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = $detail->cost - $data['Net_cost'] - $data['DiscountNet'];
                $data['subtotal'] = ($data['Net_cost'] * $data['quantity']) + ($tax_cost * $data['quantity']);
            }

            $details[] = $data;
        }

        $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        $suppliers = Provider::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'details' => $details,
            'purchase_return' => $Return_detail,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);

    } 

    //------------------ Send SMS ----------------------------------\\

    public function Send_SMS(Request $request)
    {
        $PurchaseReturn = PurchaseReturn::where('deleted_at', '=', null)->findOrFail($request->id);
        $url = url('/api/Return_Purchase_PDF/' . $request->id);
        $receiverNumber = $PurchaseReturn['provider']->phone;
        $message = "Dear" .' '.$PurchaseReturn['provider']->name." \n We are contacting you in regard to a purchase Return #".$PurchaseReturn->Ref.' '.$url.' '. "that has been created on your account. \n We look forward to conducting future business with you.";
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

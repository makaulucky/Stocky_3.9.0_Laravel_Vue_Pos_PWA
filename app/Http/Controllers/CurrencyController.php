<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{

    //------------ GET ALL Currency -----------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Currency::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();

        $currencies = Currency::where('deleted_at', '=', null)

        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $currencies->count();
        $currencies = $currencies->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'currencies' => $currencies,
            'totalRows' => $totalRows,
        ]);
    }

    //---------------- STORE NEW Currency -------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Currency::class);

        request()->validate([
            'code' => 'required',
            'name' => 'required',
            'symbol' => 'required',
        ]);

        Currency::create([
            'name' => $request['name'],
            'code' => $request['code'],
            'symbol' => $request['symbol'],
        ]);

        return response()->json(['success' => true]);

    }

    //------------ function show -----------\\

    public function show($id){
        //
        
        }

    //---------------- UPDATE Currency -------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Currency::class);

        request()->validate([
            'code' => 'required',
            'name' => 'required',
            'symbol' => 'required',
        ]);

        Currency::whereId($id)->update([
            'name' => $request['name'],
            'code' => $request['code'],
            'symbol' => $request['symbol'],
        ]);

        return response()->json(['success' => true]);

    }

    //------------ Delete Currency -----------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Currency::class);

        Currency::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Currency::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $Currency_id) {
            Currency::whereId($Currency_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true]);
    }

    //------------ GET ALL Currency WITHOUT PAGINATE -----------\\

    public function Get_Currencies()
    {
        $Currencies = Currency::where('deleted_at', null)->get(['id', 'name']);
        return response()->json($Currencies);
    }

}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // Insert some stuff
	DB::table('permissions')->insert(
		array([
			'id'    => 1,
			'name'  => 'users_view',
		],
		[
			'id'    => 2,
			'name'  => 'users_edit',
		],
		[
			'id'    => 3,
			'name'  => 'record_view',
		],
		[
			'id'    => 4,
			'name'  => 'users_delete',
		],
		[
			'id'    => 5,
			'name'  => 'users_add',
		],
		[
			'id'    => 6,
			'name'  => 'permissions_edit',
		],
		[
			'id'    => 7,
			'name'  => 'permissions_view',
		],
		[
			'id'    => 8,
			'name'  => 'permissions_delete',
		],
		[
			'id'    => 9,
			'name'  => 'permissions_add',
		],
		[
			'id'    => 10,
			'name'  => 'products_delete',
		],
		[
			'id'    => 11,
			'name'  => 'products_view',
		],
		[
			'id'    => 12,
			'name'  => 'barcode_view',
		],
		[
			'id'    => 13,
			'name'  => 'products_edit',
		],
		[
			'id'    => 14,
			'name'  => 'products_add',
		],
		[
			'id'    => 15,
			'name'  => 'expense_add',
		],
		[
			'id'    => 16,
			'name'  => 'expense_delete',
		],
		[
			'id'    => 17,
			'name'  => 'expense_edit',
		],
		[
			'id'    => 18,
			'name'  => 'expense_view',
		],
		[
			'id'    => 19,
			'name'  => 'transfer_delete',
		],
		[
			'id'    => 20,
			'name'  => 'transfer_add',
		],
		[
			'id'    => 21,
			'name'  => 'transfer_view',
		],
		[
			'id'    => 22,
			'name'  => 'transfer_edit',
		],
		[
			'id'    => 23,
			'name'  => 'adjustment_delete',
		],
		[
			'id'    => 24,
			'name'  => 'adjustment_add',
		],
		[
			'id'    => 25,
			'name'  => 'adjustment_edit',
		],
		[
			'id'    => 26,
			'name'  => 'adjustment_view',
		],
		[
			'id'    => 27,
			'name'  => 'Sales_edit',
		],
		[
			'id'    => 28,
			'name'  => 'Sales_view',
		],
		[
			'id'    => 29,
			'name'  => 'Sales_delete',
		],
		[
			'id'    => 30,
			'name'  => 'Sales_add',
		],
		[
			'id'    => 31,
			'name'  => 'Purchases_edit',
		],
		[
			'id'    => 32,
			'name'  => 'Purchases_view',
		],
		[
			'id'    => 33,
			'name'  => 'Purchases_delete',
		],
		[
			'id'    => 34,
			'name'  => 'Purchases_add',
		],
		[
			'id'    => 35,
			'name'  => 'Quotations_edit',
		],
		[
			'id'    => 36,
			'name'  => 'Quotations_delete',
		],
		[
			'id'    => 37,
			'name'  => 'Quotations_add',
		],
		[
			'id'    => 38,
			'name'  => 'Quotations_view',
		],
		[
			'id'    => 39,
			'name'  => 'payment_sales_delete',
		],
		[
			'id'    => 40,
			'name'  => 'payment_sales_add',
		],
		[
			'id'    => 41,
			'name'  => 'payment_sales_edit',
		],
		[
			'id'    => 42,
			'name'  => 'payment_sales_view',
		],
		[
			'id'    => 43,
			'name'  => 'Purchase_Returns_delete',
		],
		[
			'id'    => 44,
			'name'  => 'Purchase_Returns_add',
		],
		[
			'id'    => 45,
			'name'  => 'Purchase_Returns_view',
		],
		[
			'id'    => 46,
			'name'  => 'Purchase_Returns_edit',
		],
		[
			'id'    => 47,
			'name'  => 'Sale_Returns_delete',
		],
		[
			'id'    => 48,
			'name'  => 'Sale_Returns_add',
		],
		[
			'id'    => 49,
			'name'  => 'Sale_Returns_edit',
		],
		[
			'id'    => 50,
			'name'  => 'Sale_Returns_view',
		],
		[
			'id'    => 51,
			'name'  => 'payment_purchases_edit',
		],
		[
			'id'    => 52,
			'name'  => 'payment_purchases_view',
		],
		[
			'id'    => 53,
			'name'  => 'payment_purchases_delete',
		],
		[
			'id'    => 54,
			'name'  => 'payment_purchases_add',
		],
		[
			'id'    => 55,
			'name'  => 'payment_returns_edit',
		],
		[
			'id'    => 56,
			'name'  => 'payment_returns_view',
		],
		[
			'id'    => 57,
			'name'  => 'payment_returns_delete',
		],
		[
			'id'    => 58,
			'name'  => 'payment_returns_add',
		],
		[
			'id'    => 59,
			'name'  => 'Customers_edit',
		],
		[
			'id'    => 60,
			'name'  => 'Customers_view',
		],
		[
			'id'    => 61,
			'name'  => 'Customers_delete',
		],
		[
			'id'    => 62,
			'name'  => 'Customers_add',
		],
		[
			'id'    => 63,
			'name'  => 'unit',
		],
		[
			'id'    => 64,
			'name'  => 'currency',
		],
		[
			'id'    => 65,
			'name'  => 'category',
		],
		[
			'id'    => 66,
			'name'  => 'backup',
		],
		[
			'id'    => 67,
			'name'  => 'warehouse',
		],
		[
			'id'    => 68,
			'name'  => 'brand',
		],
		[
			'id'    => 69,
			'name'  => 'setting_system',
		],
		[
			'id'    => 70,
			'name'  => 'Warehouse_report',
		],
		[
			'id'    => 72,
			'name'  => 'Reports_quantity_alerts',
		],
		[
			'id'    => 73,
			'name'  => 'Reports_profit',
		],
		[
			'id'    => 74,
			'name'  => 'Reports_suppliers',
		],
		[
			'id'    => 75,
			'name'  => 'Reports_customers',
		],
		[
			'id'    => 76,
			'name'  => 'Reports_purchase',
		],
		[
			'id'    => 77,
			'name'  => 'Reports_sales',
		],
		[
			'id'    => 78,
			'name'  => 'Reports_payments_purchase_Return',
		],
		[
			'id'    => 79,
			'name'  => 'Reports_payments_Sale_Returns',
		],
		[
			'id'    => 80,
			'name'  => 'Reports_payments_Purchases',
		],
		[
			'id'    => 81,
			'name'  => 'Reports_payments_Sales',
		],
		[
			'id'    => 82,
			'name'  => 'Suppliers_delete',
		],
		[
			'id'    => 83,
			'name'  => 'Suppliers_add',
		],
		[
			'id'    => 84,
			'name'  => 'Suppliers_edit',
		],
		[
			'id'    => 85,
			'name'  => 'Suppliers_view',
		],
		[
			'id'    => 86,
			'name'  => 'Pos_view',
		],
		[
			'id'    => 87,
			'name'  => 'product_import',
		],
		[
			'id'    => 88,
			'name'  => 'customers_import',
		],
		[
			'id'    => 89,
			'name'  => 'Suppliers_import',
		],)
	);
    }
}

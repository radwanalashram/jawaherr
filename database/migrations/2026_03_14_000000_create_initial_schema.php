<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialSchema extends Migration
{
    public function up()
    {
        // Users
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->enum('role', ['admin','accountant','cashier','stock_manager'])->default('cashier');
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Currencies
        Schema::create('currencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 8)->unique();
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->timestamps();
        });

        // Accounts (chart)
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type');
            $table->uuid('parent_id')->nullable();
            $table->decimal('credit_limit', 18, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('accounts')->onDelete('set null');
        });

        // Cashboxes
        Schema::create('cashboxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->nullable();
            $table->uuid('currency_id')->nullable();
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->decimal('current_balance', 18, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        // Parties (customers/suppliers)
        Schema::create('parties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Items
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->decimal('purchase_price', 18, 4)->default(0);
            $table->decimal('sale_price', 18, 4)->default(0);
            $table->decimal('cost', 18, 4)->default(0);
            $table->decimal('stock_total', 18, 4)->default(0);
            $table->boolean('track_stock')->default(true);
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Item units
        Schema::create('item_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('conversion_factor', 18, 6)->default(1);
            $table->timestamps();
        });

        // Barcodes
        Schema::create('barcodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('code');
            $table->string('type')->default('code128');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->unique(['item_id','code']);
        });

        // Warehouses
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('location')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Stock levels
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('warehouse_id');
            $table->decimal('qty', 24, 6)->default(0);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->unique(['item_id','warehouse_id']);
        });

        // Stock movements
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('from_warehouse_id')->nullable();
            $table->uuid('to_warehouse_id')->nullable();
            $table->decimal('qty', 24, 6);
            $table->string('movement_type');
            $table->uuid('reference_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->unique();
            $table->string('type');
            $table->uuid('party_id')->nullable();
            $table->timestamp('date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->uuid('currency_id')->nullable();
            $table->decimal('subtotal', 24, 4)->default(0);
            $table->decimal('tax', 24, 4)->default(0);
            $table->decimal('discounts', 24, 4)->default(0);
            $table->decimal('total', 24, 4)->default(0);
            $table->string('status')->default('draft');
            $table->string('payment_type')->default('credit');
            $table->decimal('paid_amount', 24, 4)->default(0);
            $table->uuid('created_by')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('party_id')->references('id')->on('parties')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Invoice lines
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->uuid('item_id')->nullable();
            $table->text('description')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->decimal('qty', 24, 6)->default(1);
            $table->decimal('unit_price', 24, 4)->default(0);
            $table->decimal('discount', 24, 4)->default(0);
            $table->decimal('line_total', 24, 4)->default(0);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('set null');
        });

        // Vouchers
        Schema::create('vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('voucher_number')->unique()->nullable();
            $table->string('type');
            $table->uuid('party_id')->nullable();
            $table->uuid('account_id')->nullable();
            $table->uuid('cashbox_id')->nullable();
            $table->uuid('currency_id')->nullable();
            $table->decimal('amount', 24, 4);
            $table->decimal('exchange_rate', 24, 8)->default(1);
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('channel');
            $table->string('to')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        // Audit logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type')->nullable();
            $table->uuid('entity_id')->nullable();
            $table->string('action')->nullable();
            $table->uuid('user_id')->nullable();
            $table->json('changes')->nullable();
            $table->timestamps();
        });

        // App settings
        Schema::create('app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_levels');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('barcodes');
        Schema::dropIfExists('item_units');
        Schema::dropIfExists('items');
        Schema::dropIfExists('parties');
        Schema::dropIfExists('cashboxes');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('users');
    }
}
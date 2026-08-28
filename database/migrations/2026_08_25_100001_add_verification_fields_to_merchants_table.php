<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            // Owner / business fields
            $table->string('owner_name')->nullable()->after('company_name');
            $table->string('business_type')->nullable()->after('owner_name');
            
            // Verification documents
            $table->string('ic_image')->nullable()->after('address');
            $table->string('ssm_image')->nullable()->after('ic_image');
            
            // PDPA consent
            $table->boolean('consent_pdpa')->default(false)->after('ssm_image');
            
            // Superadmin approval
            $table->timestamp('verified_at')->nullable()->after('consent_pdpa');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('verified_by');
            
            // Change default status to pending_verification
            $table->string('status')->default('pending_verification')->change();
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name',
                'business_type',
                'ic_image',
                'ssm_image',
                'consent_pdpa',
                'verified_at',
                'verified_by',
                'rejection_reason',
            ]);
            $table->string('status')->default('active')->change();
        });
    }
};

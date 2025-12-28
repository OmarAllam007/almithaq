<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable (optional field)
            $table->string('email')->nullable()->change();

            // Step 1: Account Type
            $table->enum('registration_type', ['wife', 'husband'])->nullable();

            // Step 2: Basic Account Information
            $table->string('username')->unique()->nullable();
            $table->string('marriage_type')->nullable();
            $table->string('marriage_status')->nullable();
            $table->integer('age')->nullable();
            $table->integer('child_count')->default(0)->nullable();

            // Step 3: Personal Information
            $table->string('residence')->nullable();
            $table->string('nationality')->nullable();
            $table->string('city')->nullable();
            $table->string('religion')->nullable();
            $table->string('ethnicity')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->string('skin_color')->nullable();
            $table->string('body_shape')->nullable();

            // Step 4: Lifestyle & Work
            $table->string('devotion')->nullable();
            $table->string('prayer')->nullable();
            $table->string('smoking')->nullable();
            $table->string('beard')->nullable();
            $table->string('education_level')->nullable();
            $table->string('financial_status')->nullable();
            $table->string('field_of_work')->nullable();
            $table->string('job')->nullable();

            // Step 5: Additional Information
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->string('health_status')->nullable();
            $table->text('about_partner')->nullable();
            $table->text('about_self')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();

            $table->dropColumn([
                'registration_type',
                'username',
                'marriage_type',
                'marriage_status',
                'age',
                'child_count',
                'residence',
                'nationality',
                'city',
                'religion',
                'ethnicity',
                'weight',
                'height',
                'skin_color',
                'body_shape',
                'devotion',
                'prayer',
                'smoking',
                'beard',
                'education_level',
                'financial_status',
                'field_of_work',
                'job',
                'monthly_income',
                'health_status',
                'about_partner',
                'about_self',
                'full_name',
                'phone_number',
            ]);
        });
    }
};

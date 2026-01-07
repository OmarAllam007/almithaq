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
            $table->unsignedTinyInteger('registration_type')->nullable(); // ['wife', 'husband']

            // Step 2: Basic Account Information
            $table->string('username')->unique()->nullable();
            $table->unsignedTinyInteger('marriage_type')->nullable();
            $table->unsignedTinyInteger('marriage_status')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedTinyInteger('child_count')->default(0)->nullable();

            // Step 3: Personal Information
            $table->unsignedInteger('residence')->nullable();
            $table->unsignedInteger('nationality')->nullable();
            $table->unsignedTinyInteger('city')->nullable();
            $table->unsignedTinyInteger('religion')->nullable();
            $table->unsignedTinyInteger('ethnicity')->nullable();
            $table->unsignedInteger('weight')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedTinyInteger('skin_color')->nullable();
            $table->unsignedTinyInteger('body_shape')->nullable();

            // Step 4: Lifestyle & Work
            $table->unsignedTinyInteger('devotion')->nullable();
            $table->unsignedTinyInteger('prayer')->nullable();
            $table->boolean('smoking')->nullable();
            $table->boolean('beard')->nullable();
            $table->unsignedTinyInteger('education_level')->nullable();
            $table->unsignedTinyInteger('financial_status')->nullable();
            $table->unsignedTinyInteger('field_of_work')->nullable();
            $table->string('job')->nullable();

            // Step 5: Additional Information
            $table->unsignedTinyInteger('monthly_income')->nullable();
            $table->unsignedTinyInteger('health_status')->nullable();
            $table->text('about_partner')->nullable();
            $table->text('about_self')->nullable();
            $table->string('full_name')->nullable();
            $table->string('country_code', 5);
            $table->string('phone_number', 20);
            $table->boolean('is_verified')->default(false)->nullable();
            $table->boolean('is_active')->default(false)->nullable();
            $table->unique(['country_code', 'phone_number']);
            $table->softDeletes();
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
                'country_code',
                'is_verified',
                'is_active',
                'deleted_at',
            ]);
        });
    }
};

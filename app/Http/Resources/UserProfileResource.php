<?php

namespace App\Http\Resources;

use App\Models\Country;
use App\Models\Enums\BodyShape;
use App\Models\Enums\DevotionType;
use App\Models\Enums\EducationLevel;
use App\Models\Enums\FieldOfWork;
use App\Models\Enums\FinancialStatus;
use App\Models\Enums\HealthStatusType;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\Enums\MonthlyIncomeType;
use App\Models\Enums\PrayerType;
use App\Models\Enums\SkinColor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $this */
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'age' => $this->age,
            'nationality' => Country::find($this->nationality)?->only('id', 'name', 'ar_name', 'flag'),
            'residence' => Country::find($this->residence)?->only('id', 'name', 'ar_name', 'flag'),
            'registration_type' => $this->gender,
            // Raw values for form editing
            'marriage_status' => $this->marriage_status,
            'marriage_type' => $this->marriage_type,
            'health_status' => $this->health_status,
            'monthly_income' => $this->monthly_income,
            'child_count' => $this->child_count,
            'city' => $this->city,
            'religion' => 'Muslim',
            'height' => $this->height,
            'weight' => $this->weight,
            'skin_color' => $this->skin_color,
            'body_shape' => $this->body_shape,
            'devotion' => $this->devotion,
            'prayer' => $this->prayer,
            'smoking' => $this->smoking,
            'beard' => $this->beard,
            'education_level' => $this->education_level,
            'financial_status' => $this->financial_status,
            'field_of_work' => $this->field_of_work,
            'job' => $this->job,
            'about_self' => $this->about_self,
            'about_partner' => $this->about_partner,
            'is_favourite' => $this->isFavoritedBy(auth()->id()),
            'is_ignored' => $this->isIgnored(auth()->id()),
            // Display labels
            'marriage_status_label' => MarriageStatus::tryFrom($this->marriage_status)?->label(),
            'marriage_type_label' => MarriageType::tryFrom($this->marriage_type)?->label(),
            'health_status_label' => HealthStatusType::tryFrom($this->health_status)?->label(),
            'monthly_income_label' => MonthlyIncomeType::tryFrom($this->monthly_income)?->label(),
            'skin_color_label' => SkinColor::tryFrom($this->skin_color)?->label(),
            'body_shape_label' => BodyShape::tryFrom($this->body_shape)?->label(),
            'devotion_label' => DevotionType::tryFrom($this->devotion)?->label(),
            'prayer_label' => PrayerType::tryFrom($this->prayer)?->label(),
            'education_level_label' => EducationLevel::tryFrom($this->education_level)?->label(),
            'financial_status_label' => FinancialStatus::tryFrom($this->financial_status)?->label(),
            'field_of_work_label' => FieldOfWork::tryFrom($this->field_of_work)?->label(),
        ];
                return $data;
    }
}

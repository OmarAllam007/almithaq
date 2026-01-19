<?php

namespace App\Http\Resources;

use App\Models\Country;
use App\Models\Enums\BodyShape;
use App\Models\Enums\DevotionType;
use App\Models\Enums\EducationLevel;
use App\Models\Enums\FinancialStatus;
use App\Models\Enums\HealthStatusType;
use App\Models\Enums\MarriageStatus;
use App\Models\Enums\MarriageType;
use App\Models\Enums\MonthlyIncomeType;
use App\Models\Enums\PrayerType;
use App\Models\Enums\RegistrationType;
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
            'email'=> $this->email,
            'phone_number' => $this->phone_number,
            'age' => $this->age,
            'nationality' => Country::find($this->nationality)->only('name', 'ar_name', 'flag'),
            'residence' => Country::find($this->residence)->only('name', 'ar_name', 'flag'),
            'marriage_status' => MarriageStatus::from($this->marriage_status)?->label(),
            'marriage_type' => MarriageType::from($this->marriage_type)?->label(),
            'health_status' => HealthStatusType::from($this->health_status)?->label(),
            'monthly_income' => MonthlyIncomeType::from($this->monthly_income)?->label(),
            'child_count' => $this->child_count,
            'religion' => 'Muslim',
            'height' => $this->height,
            'weight' => $this->weight,
            'skin_color' => SkinColor::from($this->skin_color)?->label(),
            'body_shape' => BodyShape::from($this->body_shape)?->label(),
            'devotion' => DevotionType::from($this->devotion)?->label(),
            'prayer' => PrayerType::from($this->prayer)?->label(),
            'smoking' => $this->smoking ? 'Yes' : 'No',
            'education_level' => EducationLevel::from($this->education_level)?->label(),
            'financial_status' => FinancialStatus::from($this->financial_status)?->label(),
            'field_of_work' => 'To be implmeneted',
            'job' => $this->job,
            'about_self' => $this->about_self,
            'about_partner' => $this->about_partner,
            'is_favourite' => $this->isFavoritedBy(auth()->id()),
            'is_ignored' => $this->isIgnored(auth()->id()),
        ];

        if ($this->gender == RegistrationType::AS_HUSBAND) {
            $data['beard'] = $this->beard ? 'Yes' : 'No';
        }

        return $data;
    }
}

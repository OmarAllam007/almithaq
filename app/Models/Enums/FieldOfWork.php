<?php

namespace App\Models\Enums;

enum FieldOfWork: int
{
    case InformationTechnology = 1;
    case Engineering = 2;
    case HealthcareMedical = 3;
    case EducationTeaching = 4;
    case BusinessManagement = 5;
    case FinanceAccounting = 6;
    case SalesMarketing = 7;
    case GovernmentPublicSector = 8;
    case LawLegalServices = 9;
    case ConstructionRealEstate = 10;
    case ManufacturingIndustry = 11;
    case TransportationLogistics = 12;
    case HospitalityTourism = 13;
    case MediaDesignCreative = 14;
    case RetailCustomerService = 15;
    case AgricultureFood = 16;
    case SelfEmployedFreelance = 17;
    case NotWorkingCurrently = 18;

    public function label(): string
    {
        return match ($this) {
            self::InformationTechnology => trans('enums.field_of_work_it'),
            self::Engineering => trans('enums.field_of_work_engineering'),
            self::HealthcareMedical => trans('enums.field_of_work_healthcare'),
            self::EducationTeaching => trans('enums.field_of_work_education'),
            self::BusinessManagement => trans('enums.field_of_work_business'),
            self::FinanceAccounting => trans('enums.field_of_work_finance'),
            self::SalesMarketing => trans('enums.field_of_work_sales'),
            self::GovernmentPublicSector => trans('enums.field_of_work_government'),
            self::LawLegalServices => trans('enums.field_of_work_law'),
            self::ConstructionRealEstate => trans('enums.field_of_work_construction'),
            self::ManufacturingIndustry => trans('enums.field_of_work_manufacturing'),
            self::TransportationLogistics => trans('enums.field_of_work_transportation'),
            self::HospitalityTourism => trans('enums.field_of_work_hospitality'),
            self::MediaDesignCreative => trans('enums.field_of_work_media'),
            self::RetailCustomerService => trans('enums.field_of_work_retail'),
            self::AgricultureFood => trans('enums.field_of_work_agriculture'),
            self::SelfEmployedFreelance => trans('enums.field_of_work_freelance'),
            self::NotWorkingCurrently => trans('enums.field_of_work_not_working'),
        };
    }
}

<?php

namespace App\Models\Enums;

enum FieldOfWork: int {

    case InformationTechnology   = 1;
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
            self::InformationTechnology => 'Information Technology',
            self::Engineering => 'Engineering',
            self::HealthcareMedical => 'Healthcare / Medical',
            self::EducationTeaching => 'Education / Teaching',
            self::BusinessManagement => 'Business / Management',
            self::FinanceAccounting => 'Finance / Accounting',
            self::SalesMarketing => 'Sales / Marketing',
            self::GovernmentPublicSector => 'Government / Public Sector',
            self::LawLegalServices => 'Law / Legal Services',
            self::ConstructionRealEstate => 'Construction / Real Estate',
            self::ManufacturingIndustry => 'Manufacturing / Industry',
            self::TransportationLogistics => 'Transportation / Logistics',
            self::HospitalityTourism => 'Hospitality / Tourism',
            self::MediaDesignCreative => 'Media / Design / Creative',
            self::RetailCustomerService => 'Retail / Customer Service',
            self::AgricultureFood => 'Agriculture / Food',
            self::SelfEmployedFreelance => 'Self-Employed / Freelance',
            self::NotWorkingCurrently => 'Not Working Currently',
        };
    }   
}

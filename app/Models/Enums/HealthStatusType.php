<?php

namespace App\Models\Enums;

enum HealthStatusType: int
{
    case Healthy = 1;
    case PhysicalDisability = 2;
    case IntellectualDisability = 3;
    case PsychologicalDisability = 4;
    case HearingImpairment = 5;
    case VisualImpairment = 6;
    case Depression = 7;
    case MorbidObesity = 8;
    case SkinDiseases = 9;
    case Infertility = 10;
    case SexualDysfunction = 11;
    case Schizophrenia = 12;
    case InvisibleDisability = 13;
    case Diabetes = 14;
    case Polio = 15;
    case Quadriplegia = 16;
    case Hemiplegia = 17;
    case Epilepsy = 18;
    case AmputationOfLimb = 19;
    case DisfiguringBurns = 20;
    case Vitiligo = 21;
    case DownSyndrome = 22;
    case Psoriasis = 23;
    case SpeechDisorder = 24;
    case Leprosy = 25;
    case Dwarfism = 26;

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::PhysicalDisability => 'Physical disability',
            self::IntellectualDisability => 'Intellectual disability',
            self::PsychologicalDisability => 'Psychological disability',
            self::HearingImpairment => 'Hearing impairment',
            self::VisualImpairment => 'Visual impairment',
            self::Depression => 'Depression',
            self::MorbidObesity => 'Morbid obesity',
            self::SkinDiseases => 'Skin diseases',
            self::Infertility => 'Infertility',
            self::SexualDysfunction => 'Sexual dysfunction',
            self::Schizophrenia => 'Schizophrenia',
            self::InvisibleDisability => 'Invisible disability',
            self::Diabetes => 'Diabetes',
            self::Polio => 'Polio',
            self::Quadriplegia => 'Quadriplegia',
            self::Hemiplegia => 'Hemiplegia',
            self::Epilepsy => 'Epilepsy',
            self::AmputationOfLimb => 'Amputation of a limb',
            self::DisfiguringBurns => 'Disfiguring burns',
            self::Vitiligo => 'Vitiligo',
            self::DownSyndrome => 'Down syndrome',
            self::Psoriasis => 'Psoriasis',
            self::SpeechDisorder => 'Speech disorder',
            self::Leprosy => 'Leprosy',
            self::Dwarfism => 'Dwarfism',
        };
    }
}

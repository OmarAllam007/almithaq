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
            self::Healthy => trans('enums.health_healthy'),
            self::PhysicalDisability => trans('enums.health_physical_disability'),
            self::IntellectualDisability => trans('enums.health_intellectual_disability'),
            self::PsychologicalDisability => trans('enums.health_psychological_disability'),
            self::HearingImpairment => trans('enums.health_hearing_impairment'),
            self::VisualImpairment => trans('enums.health_visual_impairment'),
            self::Depression => trans('enums.health_depression'),
            self::MorbidObesity => trans('enums.health_morbid_obesity'),
            self::SkinDiseases => trans('enums.health_skin_diseases'),
            self::Infertility => trans('enums.health_infertility'),
            self::SexualDysfunction => trans('enums.health_sexual_dysfunction'),
            self::Schizophrenia => trans('enums.health_schizophrenia'),
            self::InvisibleDisability => trans('enums.health_invisible_disability'),
            self::Diabetes => trans('enums.health_diabetes'),
            self::Polio => trans('enums.health_polio'),
            self::Quadriplegia => trans('enums.health_quadriplegia'),
            self::Hemiplegia => trans('enums.health_hemiplegia'),
            self::Epilepsy => trans('enums.health_epilepsy'),
            self::AmputationOfLimb => trans('enums.health_amputation'),
            self::DisfiguringBurns => trans('enums.health_disfiguring_burns'),
            self::Vitiligo => trans('enums.health_vitiligo'),
            self::DownSyndrome => trans('enums.health_down_syndrome'),
            self::Psoriasis => trans('enums.health_psoriasis'),
            self::SpeechDisorder => trans('enums.health_speech_disorder'),
            self::Leprosy => trans('enums.health_leprosy'),
            self::Dwarfism => trans('enums.health_dwarfism'),
        };
    }
}

<?php

use App\Models\Enums\NotificationType;

it('resolves a translated label for every notification type', function (string $locale) {
    app()->setLocale($locale);

    foreach (NotificationType::cases() as $type) {
        expect($type->label())->not->toStartWith('home.');
    }
})->with(['en', 'ar']);

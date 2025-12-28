import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

// Declare global Swal from Metronic
declare const Swal: any;

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function showAlertError(errorMessage: string | string[], title: string = 'Validation Error'){
    let html = '';

    if (Array.isArray(errorMessage)) {
        html = '<ul class="text-start">';
        errorMessage.forEach(error => {
            html += `<li class="mb-2">${error}</li>`;
        });
        html += '</ul>';
    } else {
        html = `<p>${errorMessage}</p>`;
    }

    Swal.fire({
        title: title,
        html: html,
        icon: "error",
        buttonsStyling: false,
        confirmButtonText: "Ok, got it!",
        customClass: {
            confirmButton: "btn btn-primary"
        }
    });
}

export function showValidationErrors(errors: Record<string, string | string[]>){
    const errorMessages: string[] = [];

    Object.keys(errors).forEach(field => {
        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const error = errors[field];

        if (Array.isArray(error)) {
            error.forEach(msg => errorMessages.push(`<strong>${fieldName}:</strong> ${msg}`));
        } else {
            errorMessages.push(`<strong>${fieldName}:</strong> ${error}`);
        }
    });

    showAlertError(errorMessages, 'Please fix the following errors');
}

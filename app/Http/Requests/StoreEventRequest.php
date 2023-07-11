<?php

namespace App\Http\Requests;

use Gate;
use App\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('event_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name'       => [
                'required',
            ],
            'start_time' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'end_time'   => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'emails' => [
                'required',
                function ($attribute, $value, $fail) {
                    $emails = array_map('trim', explode(',', $value));
                    if(count($emails) > 2){
                        $fail('Max Two Email Allowed.');
                    }
                    $validator = Validator::make(['emails' => $emails], ['emails.*' => 'required|email']);
                    if ($validator->fails()) {
                        $fail('All email addresses must be valid seperated with commas(,).');
                    }
                },
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Economy\Track;

class TrackUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file|max:10240'
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $track = Track::find($this->track_id);

            if (is_null($track)) {
                $validator->errors()->add(
                    'track',
                    'Трек не найден'
                );

                return;
            }
            if (!is_null($track->path)) {
                $validator->errors()->add(
                    'track',
                    'Слот уже занят треком'
                );

                return;
            }

            $user = $this->user();
            if ($user->id !== $track->user_id) {
                $validator->errors()->add(
                    'track',
                    'Ты не владелец трека'
                );
                return;
            }

            $this->offsetSet('track', $track);
        });
    }
}

<?php

namespace Oxygencms\Uploads\Requests;

use Oxygencms\Core\Rules\ClassExists;
use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
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
     * If the class does not exist the ClassExist rule
     * will fail before the Exists rule is triggered.
     * So mind the order of the rules!
     *
     * @return array
     */
    public function rules()
    {
        // update (change intent)
        if ($this->isMethod('patch'))
        {
            $intents = array_keys(config('uploads.intents'));

            return [
                'intent' => 'nullable|string|in:'. implode(',', $intents),
            ];
        }

        $table = class_exists($this->uploadable_class)
            ? (new $this->uploadable_class)->getTable()
            : null;

        return [
            'uploadable_class' => ['required', 'string', new ClassExists],
            'uploadable_id' => "required|numeric|exists:$table,id",
            'file' => 'file',
        ];
    }
}

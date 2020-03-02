<?php

namespace Oxygencms\Uploads\Controllers;

use Oxygencms\Uploads\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Oxygencms\Core\Controllers\Controller;
use Oxygencms\Uploads\Requests\UploadRequest;

class UploadController extends Controller
{
    /**
     * Get a list of the uploads for a given uploadable.
     * Used in tinymce editor to list the uploads..
     *
     * @param Model $instance
     * @param       $id
     *
     * @return mixed
     */
    public function uploadsList(Model $instance, $id)
    {
        $uploads = $instance::with('uploads')->findOrFail($id)->uploads;

        return $uploads->map(function ($upload) {
            return [
                'title' => $upload->filename,
                'value' => $upload->public_path,
            ];
        });
    }

    /**
     * Store an uploaded file.
     *
     * todo: make thumbnails
     *
     * @param UploadRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(UploadRequest $request)
    {
        $class_name = $request->uploadable_class;

        $model_name = $this->getModelName($class_name);

        $model = $class_name::find($request->uploadable_id);

        $path = "uploads/$model_name/$model->id";

        $filename = $request->file->getClientOriginalName();

        if (Storage::exists("public/$path/$filename")) {
            return jsonNotification(
                "File '$filename' already exists.",
                'error',
                4000,
                500
            );
        }

        $upload = $model->uploads()->create([
            'path' => "$path",
            'filename' => $filename,
            'size' => $request->file->getSize(),
        ]);

        $request->file->move(storage_path("app/public/$path"), $filename);

        return response()->json([
            'model' => $upload,
            'notification' => [
                'type' => 'success',
                'text' => "File '$filename' successfully stored.",
            ],
        ]);
    }

    /**
     * @param Upload        $upload
     * @param UploadRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Upload $upload, UploadRequest $request)
    {
        $upload->update(['intent' => $request->get('intent')]);

        return jsonNotification("$upload->filename successfully updated.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Upload $upload
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Upload $upload)
    {
        $path = "public/$upload->path/$upload->filename";

        if ( ! Storage::exists($path)) {
            return jsonNotification(
                'File not found on the filesystem.',
                'error',
                4000,
                500
            );
        }

        if ( ! Storage::delete($path)) {
            return jsonNotification(
                'File can not be deleted from the filesystem.',
                'error',
                4000,
                500
            );
        }

        $upload->delete();

        return jsonNotification("File '$upload->filename' successfully deleted.");
    }

    /**
     * @param string $class_name
     * @return string
     */
    private function getModelName(string $class_name): string
    {
        $array = explode('\\', $class_name);

        return array_pop($array);
    }
}

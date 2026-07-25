<?php

namespace modules\ai_lead_manager\libraries\vapi_ai;

use CURLFile;

trait Files
{


    /**
     * List files
     *
     * @param array $filters Optional associative array of filters to apply to the file list.
     * @return array The API response containing the list of files.
     */
    public function list_files($filters = [])
    {
        $endpoint = '/file';

        if (!empty($filters)) {
            $endpoint .= '?' . http_build_query($filters);
        }

        return $this->send_request('GET', $endpoint);
    }

    /**
     * Uploads a new file to the VAPI server.
     *
     * @param array $data An associative array containing the file data to upload.
     * @return array The API response containing the uploaded file data.
     */
    public function upload_file($file_path, $file_name = null)
    {
        $mime_type = mime_content_type($file_path);
        $file_name = $file_name ?? basename($file_path);

        $file = new CURLFile($file_path);
        $file->setMimeType($mime_type);
        $file->setPostFilename($file_name);

        return $this->send_request('POST', '/file', ['file' => $file], ['content-type: multipart/form-data']);
    }

    /**
     * Retrieves a file by its ID.
     *
     * @param string $id The ID of the file to retrieve.
     * @return array The API response containing the file data.
     */
    public function get_file_by_id($id)
    {
        return $this->send_request('GET', '/file/' . $id);
    }

    /**
     * Deletes a file by its ID.
     *
     * @param string $id The ID of the file to delete.
     * @return array The API response from the deletion request.
     */
    public function delete_file($id)
    {
        return $this->send_request('DELETE', '/file/' . $id);
    }

    /**
     * Updates a file by its ID.
     *
     * @param string $id The ID of the file to update.
     * @param array $data An associative array containing the updated file data.
     * @return array The API response from the update request.
     */
    public function update_file($id, $data)
    {
        return $this->send_request('PATCH', '/file/' . $id, $data);
    }
}

<?php

namespace modules\ai_lead_manager\libraries\bland_ai;

use CURLFile;

trait Knowledgebase
{

    public function list_knowledgebase()
    {
        return $this->send_request('GET', "/v1/knowledgebases?include_text=true");
    }


    public function upload_knowledgebase($file_path, $file_name = null, $name = null, $description = null)
    {
        $mime_type = mime_content_type($file_path);
        $file_name = $file_name ?? basename($file_path);

        $file = new CURLFile($file_path);
        $file->setMimeType($mime_type);
        $file->setPostFilename($file_name);

        $data = [
            'file' => $file,
            'name' => $name,
            'description' => $description
        ];

        return $this->send_request('POST', "/v1/knowledgebases/upload", $data, ['content-type: multipart/form-data']);
    }

    public function delete_knowledgebase($id)
    {
        return $this->send_request('DELETE', "/v1/knowledgebases/" . $id);
    }
}

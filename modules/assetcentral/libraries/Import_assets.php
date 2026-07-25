<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_assets extends App_import
{
    protected $notImportableFields = ['id','is_enabled','show_project_assets_to_client','created_at','update_date'];

    protected $requiredFields = ['asset_name', 'category_id', 'asset_manager'];

    public function __construct()
    {
        $this->addItemsGuidelines();
        //todo implement custom fields importing
        parent::__construct();
    }

    public function perform()
    {
        $this->initialize();

        $databaseFields      = $this->getImportableDatabaseFields();
        $totalDatabaseFields = count($databaseFields);

        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            $duplicate = false;
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);
                $insert[$databaseFields[$i]] = $row[$i];
            }

            $insert = $this->trimInsertValues($insert);

            if (count($insert) > 0) {
                $this->incrementImported();
                $id = null;

                if (!$this->isSimulation()) {

                    if (!isset($insert['created_at'])) {
                        $insert['created_at'] = date('Y-m-d H:i:s');
                    }

                    $this->ci->db->insert(db_prefix().'assetcentral_assets', $insert);
                    $id = $this->ci->db->insert_id();
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insert);
                }

                $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'assetcentral_as');
            }

            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }

    public function formatFieldNameForHeading($field)
    {
        return parent::formatFieldNameForHeading($field);
    }

    protected function failureRedirectURL()
    {
        return admin_url('assetcentral/import_assets');
    }

    private function addItemsGuidelines()
    {
        $this->addImportGuidelinesInfo('Fields marked with * are required to enter..', true);
        $this->addImportGuidelinesInfo('For columns like "Asset manager, Category id, Location id" please look up the ID in the settings and fill it in (For Asset manager you will need to put staff id).');
        $this->addImportGuidelinesInfo('Depreciation method column can have only one of 2 values : reducing_balance or straight_line');
    }

    private function formatValuesForSimulation($values)
    {
        return $values;
    }
}
<?php

namespace GouuseCore\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use GouuseCore\Helpers\StringHelper;

/**
 * 导出excel
 */
class ExcelLib extends Lib
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 建议导出不要超过3千行
	 * @param unknown $file_name
	 * @param unknown $header
	 * @param unknown $data
	 */
	public function down($file_name, $header, $data)
	{
		$spreadsheet = new Spreadsheet();
		$header_key = [];
		$activeSheet = $spreadsheet->setActiveSheetIndex(0);
		$index = 0;
		foreach ($header as $key => $value) {
			$alp = StringHelper::getAlp($index);
			$header_key[$key] = $alp;
			$activeSheet->setCellValue($alp.'1', $value);
			$index++;
		}
		
		if (is_array($data)) {
			foreach ($data as $index => $row) {
				foreach ($header_key as $key => $alp) {
					$spreadsheet->setCellValue($alp.($index+2), $row[$key]);
				}
			}
		}
		
		//$spreadsheet->getActiveSheet()->setTitle('Simple');
		$file_names = explode('.', $file_name);
		$ext = $file_names[1] ?? '';
		if (!in_array(strtolower($ext), ['xls', 'xlsx'])) {
			$file_name = $file_name . '.xlsx';
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$file_name.'"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		
	}
	
	/**
	 * $data = [
	 *      'sheet_name' => '',
	 *      'header' => [],
	 *      'data' => []
	 * ];
	 * @param unknown $file_name
	 * @param unknown $data
	 */
	public function downMoreSheet($file_name, $data)
	{
		$spreadsheet = new Spreadsheet();
		$page_index = 0;
		$activeSheet = $spreadsheet->setActiveSheetIndex(0);
		foreach ($data as $page_index => $page_info) {
			$sheet_name = $page_info['sheet_name'] ?? 'sheet' . $page_index;
			if ($page_index > 0) {
				$activeSheet= clone $spreadsheet->getActiveSheet();
			}
			$header_key = [];
			$header = $page_info['header'] ?? [];
			$page_data = $page_info['data'] ?? [];
			$index = 0;
			foreach ($header as $key => $value) {
				$alp = StringHelper::getAlp($index);
				$header_key[$key] = $alp;
				$activeSheet->setCellValue($alp.'1', $value);
				$index++;
			}
			
			if (is_array($page_data)) {
				foreach ($page_data as $index => $row) {
					foreach ($header_key as $key => $alp) {
						$activeSheet->setCellValue($alp.($index+2), $row[$key]);
					}
				}
			}
			if ($page_index > 0) {
				$activeSheet->setTitle($sheet_name);
				$spreadsheet->addSheet($activeSheet);
			} else {
				$spreadsheet->getActiveSheet()->setTitle($sheet_name);
			}
			$page_index++;
		}
		$spreadsheet->setActiveSheetIndex(0);
		
		$file_names = explode('.', $file_name);
		$ext = $file_names[1] ?? '';
		if (!in_array(strtolower($ext), ['xls', 'xlsx'])) {
			$file_name = $file_name . '.xlsx';
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$file_name.'"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit();
	}
	
	
	
}
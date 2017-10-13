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
	public function down($file_name, $header, $data, $all_styles = [])
	{
		$spreadsheet = new Spreadsheet();
		$header_key = [];
		$activeSheet = $spreadsheet->setActiveSheetIndex(0);
		$index = 0;
		foreach ($header as $key => $value) {
			$styles = null;
			if (is_array($value)) {
				$styles = $value['style'] ?? [];//样式
				$format = $value['formart'] ?? '';//格式
				$line  = $value['line'];//标题行数
				$val = $value['value'] ?? '';//值
			}
			if(isset($value['is_check'])){
				$index = 0;
			}
			$alp = StringHelper::getAlp($index);
			$index++;
			$header_key[$key] = $alp;
			$cell = $activeSheet->setCellValue($alp.$line, $val);
			if ($styles) {
				$style_excel = $activeSheet->getStyle($alp.$line);
				foreach ($styles as $key => $style) {
					/*if ($key == 'width') {
					 $cell->setWidth($style);
					 } else if ($key == 'height') {
					 $cell->setHeight($style);
					 } else */if ($key == 'bold') {
					$style_excel->getFont()->setBold($style);
				}  else if ($key == 'font-size') {
					$style_excel->getFont()->setSize($style);
				}  else if ($key == 'color') {
					$style_excel->getFont()->getColor()->setRGB($style);
				}  else if ($key == 'fill') {
					$style_excel->getFill()->getStartColor()->setARGB($style);;
				}
				}
			}
		}
		$number = $line;
		if (is_array($data)) {
			foreach ($data as $index => $row) {
				foreach ($header_key as $key => $alp) {
					
					$styles = null;
					$value = $col_data = $row[$key] ?? '';
					if (is_array($col_data)) {
						$styles = $col_data['style'] ?? [];//样式
						$format = $col_data['formart'] ?? '';//格式
						$value = $col_data['value'] ?? '';//值
					}
					// if (is_numeric($value) && strlen($value) > 9) {
					// 	$value= html_entity_decode("&iuml;&raquo;&iquest;".$value);
					// }
					if (isset($col_data['formula1'])) {
						//下拉选择
						$objValidation1 = $activeSheet->getCell($alp.($index+$number+1))->getDataValidation();
						$objValidation1_c = $objValidation1->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST )
						->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION )
						->setAllowBlank(false)
						->setShowInputMessage(true)
						->setShowErrorMessage(true)
						->setShowDropDown(false);
						//->setErrorTitle('输入的值有误')
						//->setError('您输入的值不在下拉框列表内.')
						$PromptTitle = $col_data['formula1']['PromptTitle'] ?? '';
						$Prompt = $col_data['formula1']['Prompt'] ?? '';
						if ($PromptTitle) {
							$objValidation1_c->setPromptTitle($PromptTitle);
						}
						if ($Prompt) {
							$objValidation1_c->setPrompt($Prompt);
						}
						$objValidation1_c->setFormula1('"' . $value . '"');
					} else {
						$activeSheet->setCellValue($alp.($index+$number+1), $value);
						if ($styles) {
							$style_excel = $activeSheet->getStyle($alp.($index+$number+1));
							foreach ($styles as $key => $style) {
								/*if ($key == 'width') {
								 $cell->setWidth($style);
								 } else if ($key == 'height') {
								 $cell->setWidth($style);
								 } else */if ($key == 'bold') {
								$style_excel->getFont()->setBold($style);
							}  else if ($key == 'font-size') {
								// $style_excel->setBold($style);
							}  else if ($key == 'color') {
								$style_excel->getFont()->getColor()->setRGB($style);
							}
							}
						}
					}
					
				}
			}
		}
		if (is_array($all_styles)) {
			foreach ($all_styles as $key=>$style) {
				if ($key == 'width') {
					if (is_array($style)) {
						foreach ($style as $cell => $width) {
							$activeSheet->getColumnDimension($cell)->setWidth($width);
						}
					}
				} else if ($key == 'height') {
					if (is_array($style)) {
						foreach ($style as $row => $height) {
							$spreadsheet->getActiveSheet()->getRowDimension($row)->setRowHeight($height);
						}
					}
				} else if ($key == 'merge') {
					if (is_array($style)) {
						foreach ($style as $merge => $unit) {
							$spreadsheet->getActiveSheet()->mergeCells($unit);
						}
					}
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
			$sheet_name = $page_info['sheet_name'] ?? 'sheet' . 'Sheet'.$page_index;
            if ($page_index) {
                $spreadsheet->createSheet();
            }
            $activeSheet = $spreadsheet->setActiveSheetIndex($page_index);
			$header_key = [];
			$header = $page_info['header'] ?? [];
			$page_data = $page_info['data'] ?? [];
			$all_styles = $page_info['styles'] ?? [];
			$index = 0;
			foreach ($header as $key => $value) {
				$styles = null;
				if (is_array($value)) {
					$styles = $value['style'];//样式
					$format = $value['formart'];//格式
					$value = $value['value'];//值
				}
				$alp = StringHelper::getAlp($index);
				$header_key[$key] = $alp;
				$activeSheet->setCellValue($alp.'1', $value);
				if ($styles) {
					$style_excel = $activeSheet->getStyle($alp.'1');
					foreach ($styles as $key => $style) {
						if ($key == 'width') {
							$style_excel->setWidth($style);
						} else if ($key == 'height') {
							$style_excel->setWidth($style);
						} else if ($key == 'bold') {
							$style_excel->setBold($style);
						}  else if ($key == 'font-size') {
							$style_excel->setBold($style);
						}
					}
				}
				$index++;
			}
			
			if (is_array($page_data)) {
				foreach ($page_data as $index => $row) {
					foreach ($header_key as $key => $alp) {
						$styles = null;
						$value = $col_data = $row[$key] ?? '';
						if (is_array($col_data)) {
							$styles = $col_data['style'] ?? [];//样式
							$format = $col_data['formart'] ?? '';//格式
							$value = $col_data['value'] ?? '';//值
						}
						$activeSheet->setCellValue($alp.($index+2), $value);
						if ($styles) {
							$style_excel = $activeSheet->getStyle($alp.($index+2));
							foreach ($styles as $key => $style) {
								if ($key == 'width') {
									$style_excel->setWidth($style);
								} else if ($key == 'height') {
									$style_excel->setWidth($style);
								} else if ($key == 'bold') {
									$style_excel->setSize($style);
								}  else if ($key == 'font-size') {
									$style_excel->setBold($style);
								}
							}
						}
					}
				}
			}
			if (is_array($all_styles)) {
				foreach ($all_styles as $key=>$style) {
					if ($key == 'width') {
						if (is_array($style)) {
							foreach ($style as $cell => $width) {
								$activeSheet->getColumnDimension($cell)->setWidth($width);
							}
						}
						
					} else if ($key == 'height') {
						if (is_array($style)) {
							foreach ($style as $row => $height) {
								$activeSheet->getRowDimension($cell)->setRowHeight($height);
							}
						}
						
					}
				}
			}
			if ($page_index > 0) {
				$activeSheet->setTitle($sheet_name);
				//$spreadsheet->addSheet($activeSheet);
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
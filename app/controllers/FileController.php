<?php
class FileController extends ControllerBase {
	public $entityName = 'File';
	
	// переменные для структурированного ответа
	public $data = array('dbg' => '');
	public $error = array('messages' => array());
	public $success = array('messages' => array());
	
	//public $entity_upload_directory = 'default/';
	public $files_upload_directory = 'upload/files/default/';
	
	public function initialize() {
		parent::initialize();
	}
	
	/*
	* Удаляет файл
	*/		
	public function deleteAction() {
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		//$this->data['_REQUEST'] = json_encode($_REQUEST);
		
		if(!isset($_REQUEST['files']) || !isset($_REQUEST['parent_entity_name']) || !isset($_REQUEST['parent_entity_id']) || !isset($_REQUEST['parent_entity_field'])) {
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => "Не получен идентификатор файла или данные основной сущности"
			];
		}
		else {
			//$files = $this->filter->sanitize(urldecode($_REQUEST['files']), ['trim', "string"]);
			$files = $this->filter->sanitize($_REQUEST['files'], ['trim', "string"]);
			$entityID = $this->filter->sanitize(urldecode($_REQUEST['parent_entity_id']), ['trim', "int"]);
			$entityName = $this->filter->sanitize(urldecode($_REQUEST['parent_entity_name']), ['trim', "string"]);
			$entityField = $this->filter->sanitize(urldecode($_REQUEST['parent_entity_field']), ['trim', "string"]);
			
			if($entityName == '' || $entityField == '' || $entityID == '') {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => "Данные основной сущности переданы в неверном формате"
				];
			}
			else {
				foreach($files as $fileID) {
					if($fileID == '') {
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => "Идентификатор файла передан в неверном формате"
						];
					}
					else {
						$file = File::findFirst( array(
							"conditions" => "id = ?1",
							"bind" => array(1 => $fileID)
						));
						$entity = false;
						$entity = $entityName::findFirst(["conditions" => "id = ?1", "bind" => array(1 => $entityID)]);
						$fcWithOneFile = false;

						// если файл найден
						if($file != false) {
							// в транзакции удаляем ссылающиеся на файл сущности, а потом и сам файл
							// открываем транзакцию
							$this->db->begin();
							// удаляем ссылки
							$fc = false;
							$fc = FileCollection::findFirst(["conditions" => "file_id = ?1", "bind" => array(1 => $fileID)]);
							// коллекиция для проверки доп. ссылок
							$fc2 = false;
							$fc2 = FileCollection::findFirst(["conditions" => "collection_id = ?1 AND file_id <> ?2", "bind" => array(1 => $entity->$entityField, 2 => $fileID)]);
							if($fc2!==false && count($fc2)==0) $fcWithOneFile = true;
							// если коллекция не найдена
							if($fc === false) {
								$dbMessages = '';
								foreach ($fc->getMessages() as $message) {
									$dbMessages .= "<li>" . $message . "</li>";
								}
								$this->error['messages'][] = [
									'title' => "Не удалось удалить коллекцию ссылок на файл " . $file->name,
									'msg' => "<ul>" . $dbMessages . "</ul>"
								];
							}
							// если в коллекции нет других файлов, то пытаемся удалить коллекцию 
							else if($fcWithOneFile && $fc->delete() === false) {
								$dbMessages = '';
								foreach ($fc->getMessages() as $message) {
									$dbMessages .= "<li>" . $message . "</li>";
								}
								$this->error['messages'][] = [
									'title' => "Не удалось удалить коллекцию ссылок на файл " . $file->name,
									'msg' => "<ul>" . $dbMessages . "</ul>"
								];
							}
							else {
								//$this->logger->log('Удалена FileCollection: ' . json_encode($fc));
								// если коллекция удалена полностью, удаляем ссылку из сущности на коллекцию
								//$this->data['dbg'] = json_encode($entity);
								
								if(!$entity) {
									$this->error['messages'][] = [
										'title' => "Не удалось удалить файл" . $file->name,
										'msg' => "Не найдена связанная сущность"
									];
								}
								else {
									if($fcWithOneFile) $entity->$entityField = NULL;
									//$this->logger->log("fc2 count = " . count($fc2));
									// обновляем сущность, если удалили коллекцию
									if($fcWithOneFile && $entity->update() === false) {
										$dbMessages = '';
										foreach ($n->getMessages() as $message) {
											$dbMessages .= "<li>" . $message . "</li>";
										}
										$this->error['messages'][] = [
											'title' => "Не обновлена связанная сущность",
											'msg' => "<ul>" . $dbMessages . "</ul>"
										];
									}
									else {
										// если связи удалены, удаляем саму сущность
										if ($file->delete() == false) {
											$dbMessages = '';
											foreach ($n->getMessages() as $message) {
												$dbMessages .= "<li>" . $message . "</li>";
											}
											$this->error['messages'][] = [
												'title' => "Не удалось удалить файл name=" . $file->name,
												'msg' => "<ul>" . $dbMessages . "</ul>"
											];
										}
										else {
											// БД почищена, теперь удаляем файл на диске
											try {
												$res = array_map("unlink", glob(__DIR__ . '/../../public/' . $file->directory . $file->name));
												$key = 1;
												foreach($res as $r) { if(!$r) $key = 0; break; }
											}
											catch (Exception $e) {
												$this->error['messages'][] = [
													'title' => "Ошибка",
													'msg' => "Файл не удален из файлового хранилища"
												];
												$key = false;
											}
											if($key) {
												// файл на диске удален
												$this->success['messages'][] = [
													'title' => "Успех",
													'msg' => "Файл " . $file->name . " удален"
												];
											}
											else {
												$this->error['messages'][] = [
													'title' => "Ошибка",
													'msg' => "Файл не удален из файлового хранилища"
												];
											}
										}
									}
								}
							}
							if(count($this->error['messages']) == 0) {
								$this->db->commit();
							}
							else $this->db->rollback();
						}
						else {
							$this->error['messages'][] = [
								'title' => "Ошибка",
								'msg' => "Файл с ID=" . $fileID . " не найден в БД"
							];
						}
					}
				}
			}
		}
		if(isset($this->error['messages']) && count($this->error['messages'])>0) $this->data['error'] = $this->error;
		if(isset($this->success['messages']) && count($this->success['messages'])>0) $this->data['success'] = $this->success;
		echo json_encode($this->data);
	}
	
	/*
	* Загружает файл
	*/		
	public function uploadAction() {
		$id = null;
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
		
		//$this->data['rq'] = json_encode($this->request);
		
		if(!isset($_REQUEST['parent_entity_id']) || !isset($_REQUEST['parent_entity_name'])) {
			$this->error['messages'][] = [
				'title' => "Ошибка",
				'msg' => "Не получен идентификатор файла или наименование основной сущности"
			];
		}
		else {
			$filt = new Phalcon\Filter();
			$pe_id = $filt->sanitize(urldecode($_REQUEST['parent_entity_id']), "int");
			$pe_name = $filt->sanitize(urldecode($_REQUEST['parent_entity_name']), "string");
			
			if(isset($_REQUEST['parent_entity_field']) && $_REQUEST['parent_entity_field'] != '') $pe_field = $filt->sanitize(urldecode($_REQUEST['parent_entity_field']), "string"); else $pe_field = null;
			//$get_rel_obj_method_name = 'get' . $filt->sanitize(urldecode($_REQUEST['entity']), "string") . 'File';
		
		
			// проверяем, имеет ли право пользователь загружать изображения для данной сущности
			if(!$this->acl->isAllowed($this->userData['role_id'], $pe_name, 'upload')) {
				$this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => "Вы не имеете права загружать файлы данной сущности (" . $pe_name . ")"
				];
			}
			else {
				// читаем настройки из БД
				/*$this->settings = Setting::find(array(
					"code IN ({codes:array})",
					"bind" => ["codes" => ["files_upload_directory",  mb_strtolower($pe_name) . "_upload_directory"]],
					"limit" => 2
				));
				// заводим переменные для настроек
				foreach ($this->settings as $set) {
					if($set->code == 'files_upload_directory') $this->files_upload_directory = $set->value;
					else if($set->code == (mb_strtolower($pe_name) . '_upload_directory')) $this->entity_upload_directory = $set->value;
				}*/
				$this->files_upload_directory = $this->config->application->filesUploadDirectory . mb_strtolower($pe_name) . '/';
				//$this->entity_upload_directory = ;
				
				//$this->logger->log(json_encode($this->settings));
		
				if(isset($pe_id) && isset($pe_name)) {
					// проверяем, чтобы в БД была такая сущность
					$e = $pe_name::findFirst(array(
						"conditions" => "id = ?1",
						"bind" => array(1 => $pe_id)
					));
					if ($e == false) {
						$this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => "Не найдена сущность, для которой загружаются данные"
						];
					}
					else {	
						$fileCopied = true;
						// Проверяем, чтобы файл загрузился
						if ($this->request->hasFiles() == true) {
							$this->data['files'] = array();
							try {
								// готовим дирректорию
								$output_dir = $this->files_upload_directory . $pe_id . "/";
								// если папки нет, то создаем
								$dir = './' . mb_substr($output_dir, 0, -1);
								if(!is_dir($dir)) mkdir($dir, 0700, true);
								
								// проходимся по списку передаваемых файлов
								$uploadedFiles = $this->request->getUploadedFiles();
								$this->data['dbg'] = count($uploadedFiles);
								foreach ($uploadedFiles as $file) {
									// для переименования фала берем первые 5 символов оригинального имени
									$arr = explode(".", $file->getName());
									
									$ext = $arr[count($arr)-1];
									//$new_name = mb_strtolower($pe_name) . '_' . $pe_id . '_' . mb_substr($arr[count($arr)-2], 0, 5) . '.' . $ext;
									$new_name = mb_strtolower($pe_name) . '_' . $pe_id . '_' . rand() . '.' . $ext;
									$this->logger->log('new_name = ' . $new_name);
									// Перемещаем файл в папку
									$file->moveTo($output_dir . $new_name);
									// Выводим детали в ответе
									$this->data['files'][] = array (
										'name' => $new_name,
										'directory' => $output_dir,
										'size' => $file->getSize(),
										'key' => $file->getKey(),
										//'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/' . $output_dir . $new_name,
										'url' => '/' . $output_dir . $new_name,
									);
								}
							}
							catch(Exception $e) {
								$fileCopied = false;
								$this->logger->log('При загрузке файла позникло исключение: ' .  $e->getMessage() . "\nЗапрос: " . json_encode($this->request));
							}
							if(!$fileCopied) {
								$this->error['messages'][] = [
									'title' => "Ошибка",
									'msg' => "Ошибка при обработке файла"
								];
							}
							else {
								// открываем транзакцию
								$this->db->begin();
								
								$fileCollectionID = 0;
								// провверяем, чтобы была коллекция файлов
								if($e->$pe_field) $fileCollectionID = $e->$pe_field;
								else $fileCollectionID = FileCollection::maximum(["column" => "collection_id"])+1;
								
								// добавляем новые файлы
								foreach ($this->data['files'] as &$file) {
									// создаем файл
									$f = new File();
									$f->name = $file['name'];
									$f->directory = $file['directory'];
									//$this->data['dbg2'] = json_encode($f);
									$this->logger->log(json_encode($f));
									
									// сохраняем файл
									if($f->create() == false) {
										$dbMessages = '';
										foreach ($f->getMessages() as $message) {
											$dbMessages .= "<li>" . $message . "</li>";
										}
										$this->error['messages'][] = [
											'title' => "Не удалось фохранить файл name=" . $f->name,
											'msg' => "<ul>" . $dbMessages . "</ul>"
										];
										break;
									}
									else $file['id'] = $f->id;
									
									// создаем для него запись в коллекции
									$fileCollection = new FileCollection();
									$fileCollection->collection_id = $fileCollectionID;
									$fileCollection->file_id = $f->id;
									if($fileCollection->create() == false) {
										$dbMessages = '';
										foreach ($fileCollection->getMessages() as $message) {
											$dbMessages .= "<li>" . $message . "</li>";
										}
										$this->error['messages'][] = [
											'title' => "Не удалось добавить в коллекцию файл name=" . $f->name,
											'msg' => "<ul>" . $dbMessages . "</ul>"
										];
										break;
									}
									
									// прописываем коллекцию в сущности
									$e->$pe_field = $fileCollectionID;
									if($e->update() == false) {
										$dbMessages = '';
										foreach ($n->getMessages() as $message) {
											$dbMessages .= "<li>" . $message . "</li>";
										}
										$this->error['messages'][] = [
											'title' => "Не удалось обновить запись сущности в БД",
											'msg' => "<ul>" . $dbMessages . "</ul>"
										];
									}
									else {
										$this->success['messages'][] = [
											'title' => "Операция успешна",
											'msg' => "Файл " . $f->name . " сохранен"
										];
									}
								}
								
								if(count($this->error['messages'])==0) {
									$this->db->commit();
								}
								else {
									$this->db->rollback();
									$this->error['messages'][] = [
										'title' => "Откат транзакции",
										'msg' => "Сохраненные записи файлов удалены"
									];
									// TODO. Удалить все сохраненные файлы
								}
							}
						}
						/*else $this->error['messages'][] = [
							'title' => "Ошибка",
							'msg' => "Не получен файл"
						];*/
					}
				}
				else $this->error['messages'][] = [
					'title' => "Ошибка",
					'msg' => "Не получен один из параметров: parent_entity_id, parent_entity_name"
				];
			}
		}
		if(isset($this->error['messages']) && count($this->error['messages'])>0) $this->data['error'] = $this->error;
		if(isset($this->success['messages']) && count($this->success['messages'])>0) $this->data['success'] = $this->success;
		echo json_encode($this->data);
	}
}

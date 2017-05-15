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
		
		if ($this->request->isPost()) {
			$rq = $this->request->getJsonRawBody();
			
			$filt = new Phalcon\Filter();
			$entityID = null;
			$entityName = null;
			$entityField = null;
			if(isset($rq->parent_entity_id)) $entityID = $filt->sanitize(urldecode($rq->parent_entity_id), ["trim", "int"]);
			if(isset($rq->parent_entity_name)) $entityName = $filt->sanitize(urldecode($rq->parent_entity_name), ["trim", "string"]);
			if(isset($rq->parent_entity_field)) $entityField = $filt->sanitize(urldecode($rq->parent_entity_field), ["trim", "string"]);
			
			if(($entityID == null || $entityID == '') || ($entityName == null || $entityName == '') || ($entityField == null || $entityField == '')) {
				$this->error['messages'][] = [
					'title' => $this->t->_("msg_error_title"),
					'msg' => "Не получен идентификатор файла или данные основной сущности"
				];
			}
			else if(isset($rq->files)) {
				// находим сущность, у которой удаляем файл
				$entity = $entityName::findFirst(["conditions" => "id = ?1", "bind" => [1 => $entityID]]);
				if($entity) {
					foreach($rq->files as $fileItemID) {
						$fileID = $filt->sanitize(urldecode($fileItemID), ["trim", "int"]);
						// плохие id файлов просто пропускаем
						if($fileID == null || $fileID == '') continue; //unset($rq->files[$fileItemID]);
						else {
							$file = File::findFirst(["conditions" => "id = ?1", "bind" => [1 => $fileID]]);

							// если файл найден
							if($file && count($file) > 0) {
								// в транзакции удаляем ссылающиеся на файл сущности, а потом и сам файл
								// открываем транзакцию
								// выбираем все файлы коллекции
								$collection = FileCollection::find(["conditions" => "collection_id = ?1 AND file_id = ?2", "bind" => [1 => $entity->$entityField, 2 => $file->id]]);
								// если нашли 1 или более одного и того же файла в коллекции
								if($collection && count($collection) > 0) {
									$this->db->begin();
									
									//удаляем все коллекции, которые на него смотрели
									$this->logger->log(__METHOD__ . '. collection count = ' . count($collection));
									// проходим по файлам и удаляем их
									foreach($collection as $collectionFile) {
										if($collectionFile->delete() === false) {
											$dbMessages = '';
											foreach ($fc->getMessages() as $message) {
												$dbMessages .= "<li>" . $message . "</li>";
											}
											$dbMessages = $dbMessages;
											$this->error['messages'][] = [
												'title' => $this->t->_("msg_error_title"),
												'msg' => "Не удалось удалить из базы данных коллекцию ссылок на файл " . $file->name . ": " . "<ul>" . $dbMessages . "</ul>",
											];
										}
									}
									$this->logger->log(__METHOD__ . '. collection count after delete = ' . count($collection));
									// если в коллекции не осталось файлов, то удаляем ссылку на коллекцию из сущности
									if(count($collection) == 0) {
										$entity->$entityField = null;
										if($entity->update() === false) {
											$dbMessages = '';
											foreach ($n->getMessages() as $message) {
												$dbMessages .= "<li>" . $message . "</li>";
											}
											$this->error['messages'][] = [
												'title' => $this->t->_("msg_error_title"),
												'msg' => "Не обновлена связанная сущность: <ul>" . $dbMessages . "</ul>",
											];
										}
									}
									
									// удаляем сам файл из БД
									// проверяем, чтобы на файл не ссылалась другая коллекция или на файл на диске нессылалась запись из БД
									$fc = FileCollection::findFirst(["conditions" => "file_id = ?1 AND collection_id <> ?2", "bind" => [1 => $fileID, 2 => $entity->$entityField]]);
									$otherFiles = File::find(["conditions" => "name = '?1'", "bind" => [1 => $file->name]]);
									if((!$fc || count($fc) == 0) ?? (!$otherFiles || count($otherFiles) == 0)) {
										// удаляем файл из БД
										if($file->delete() === false) {
											$dbMessages = '';
											foreach ($fc->getMessages() as $message) {
												$dbMessages .= "<li>" . $message . "</li>";
											}
											$this->error['messages'][] = [
												'title' => $this->t->_("msg_error_title"),
												'msg' => "Не удалось удалить из базы данных файл " . $file->name . ": " . "<ul>" . $dbMessages . "</ul>",
											];
										}
										else {
											// файл удален, БД почищена, теперь удаляем файл на диске
											try {
												$res = array_map("unlink", glob(__DIR__ . '/../../public/' . $file->directory . $file->name));
												$key = 1;
												foreach($res as $r) { if(!$r) $key = 0; break; }
											}
											catch (Exception $e) {
												$this->error['messages'][] = [
													'title' => $this->t->_("msg_error_title"),
													'msg' => $this->t->_("msg_error_file_not_deleted_from_hdd"),
												];
												$key = false;
											}
											if($key) {
												// файл на диске удален
												$this->success['messages'][] = [
													'title' => $this->t->_("msg_success_title"),
													'msg' => $this->t->_("msg_success_file_deleted", ['file_name' => $file->name]),
												];
											}
											else {
												$this->error['messages'][] = [
													'title' => $this->t->_("msg_error_title"),
													'msg' => $this->t->_("msg_error_file_not_deleted_from_hdd", ['file_name' => $file->name]),
												];
											}
										}
									}
									if(count($this->error['messages']) == 0) {
										$this->db->commit();
									}
									else $this->db->rollback();
								}
							}
							else {
								$this->logger->error(__METHOD__ . 'Файл с ID=' . $fileID . ' не найден в БД');
								$this->error['messages'][] = [
									'title' => $this->t->_("msg_error_title"),
									'msg' => $this->t->_("msg_error_file_not_deleted", ['file_name' => $file->name]),
								];
							}
						}
					}
				}
				else {
					$this->error['messages'][] = [
						'title' => $this->t->_("msg_error_title"),
						'msg' => $this->t->_("msg_error_files_not_deleted"),
					];
				}
			}
		}
		else {
			$this->error['messages'][] = [
				'title' => $this->t->_("msg_error_title"),
				'msg' => $this->t->_("msg_error_post_is_expected"),
			];
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
		if ($this->request->isPost()) {
			//$rq = $this->request->getJsonRawBody();
		
			if(!isset($_REQUEST['parent_entity_id']) || !isset($_REQUEST['parent_entity_name'])) {
				$this->error['messages'][] = [
					'title' => $this->t->_("msg_error_title"),
					'msg' => "Не получен идентификатор файла или наименование основной сущности"
				];
			}
			else {
				$filt = new Phalcon\Filter();
				$pe_id = $filt->sanitize(urldecode($_REQUEST['parent_entity_id']), ["trim", "int"]);
				$pe_name = $filt->sanitize(urldecode($_REQUEST['parent_entity_name']), ["trim", "string"]);
				
				if(isset($_REQUEST['parent_entity_field']) && $_REQUEST['parent_entity_field'] != '') $pe_field = $filt->sanitize(urldecode($_REQUEST['parent_entity_field']), "string"); else $pe_field = null;
				//$get_rel_obj_method_name = 'get' . $filt->sanitize(urldecode($_REQUEST['entity']), "string") . 'File';
			
			
				// проверяем, имеет ли право пользователь загружать изображения для данной сущности
				if(!$this->acl->isAllowed($this->userData['role_id'], strtolower($pe_name), 'upload')) {
					$this->error['messages'][] = [
						'title' => $this->t->_("msg_error_title"),
						'msg' => "Вы не имеете права загружать файлы данной сущности (" . strtolower($pe_name) . ")"
					];
				}
				else {
					$this->files_upload_directory = $this->config->application->filesUploadDirectory . mb_strtolower($pe_name) . '/';
					//$this->entity_upload_directory = ;
					
					//$this->logger->log(json_encode($this->settings));
			
					if(isset($pe_id) && isset($pe_name)) {
						// проверяем, чтобы в БД была такая сущность
						$entity = $pe_name::findFirst(array(
							"conditions" => "id = ?1",
							"bind" => array(1 => $pe_id)
						));
						//$this->logger->log(__METHOD__ . '. entity = ' . json_encode($entity));
						if ($entity == false) {
							$this->error['messages'][] = [
								'title' => $this->t->_("msg_error_title"),
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
									//$this->data['dbg'] = count($uploadedFiles);
									foreach ($uploadedFiles as $file) {
										// для переименования фала берем первые 5 символов оригинального имени
										$arr = explode(".", $file->getName());
										
										$ext = $arr[count($arr)-1];
										//$new_name = mb_strtolower($pe_name) . '_' . $pe_id . '_' . mb_substr($arr[count($arr)-2], 0, 5) . '.' . $ext;
										$new_name = mb_strtolower($pe_name) . '_' . $pe_id . '_' . rand() . '.' . $ext;
										//$this->logger->log('new_name = ' . $new_name);
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
										'title' => $this->t->_("msg_error_title"),
										'msg' => "Ошибка при обработке файла"
									];
								}
								else {
									// открываем транзакцию
									$this->db->begin();
									
									$fileCollectionID = 0;
									// провверяем, чтобы была коллекция файлов
									if($entity->$pe_field) $fileCollectionID = $entity->$pe_field;
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
										$entity->$pe_field = $fileCollectionID;
										if($entity->update() == false) {
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
						'title' => $this->t->_("msg_error_title"),
						'msg' => "Не получен один из параметров: parent_entity_id, parent_entity_name"
					];
				}
			}
		}
		else {
			$this->error['messages'][] = [
				'title' => $this->t->_("msg_error_title"),
				'msg' => "Запрошено сохранение данных методом GET. Ожидается метод POST"
			];
		}
		if(isset($this->error['messages']) && count($this->error['messages'])>0) $this->data['error'] = $this->error;
		if(isset($this->success['messages']) && count($this->success['messages'])>0) $this->data['success'] = $this->success;
		echo json_encode($this->data);
	}
}

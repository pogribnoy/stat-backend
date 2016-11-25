<?php
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Logger\Adapter\File as FileAdapter;

/**
 * NotFoundPlugin
 *
 * Handles not-found controller/actions
 */
class NotFoundPlugin extends Plugin {
	/**
	 * This action is executed before execute any action in the application
	 *
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeException(Event $event, MvcDispatcher $dispatcher, $exception) {
		//var_dump($dispatcher);
		// инициализируем лог
		$logger = new FileAdapter(APP_PATH . "/app/logs/errors.log", array('mode' => 'a'));
		//$logger->log('ctrler = ' . $dispatcher->getControllerName ());
		$str = "NotFoundPlugin. Ошибка 500: ";
		if ($exception instanceof DispatcherException) {
			switch ($exception->getCode()) {
				case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
					$str = 'Не найден обработчик (EXCEPTION_HANDLER_NOT_FOUND)';
					break;
				case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
					$str = 'Не найден обработчик (EXCEPTION_ACTION_NOT_FOUND)';
					break;
				case Dispatcher::EXCEPTION_NO_DI:
					$str = 'Не найден инжектор зависимостей (EXCEPTION_NO_DI)';
					break;
				case Dispatcher::EXCEPTION_CYCLIC_ROUTING:
					$str = 'Циклические ссылки (EXCEPTION_CYCLIC_ROUTING)';
					break;
				case Dispatcher::EXCEPTION_INVALID_HANDLER:
					$str = 'Обработчик неверный (EXCEPTION_INVALID_HANDLER)';
					break;
				case Dispatcher::EXCEPTION_INVALID_PARAMS:
					$str = 'Параметры неверны (EXCEPTION_INVALID_PARAMS)';
					break;
			}
			$dispatcher->forward(array(
				'controller' => 'errors',
				'action' => 'show404',
			));
			$logger->log("NotFoundPlugin. Ошибка 404. " . $str . ". " . $exception);
			return false;
		}
		$dispatcher->forward(array(
			'controller' => 'errors',
			'action'     => 'show500'
		));
		$logger->log($str . $exception);
		return false;
	}
}

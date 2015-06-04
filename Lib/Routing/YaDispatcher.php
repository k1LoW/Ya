<?php

App::uses('Dispatcher', 'Routing');
App::uses('InactiveControllerException', 'Caco.Error');
App::build(
    [
        'Controller' => [
            APP.'Ya/Controller/',
        ],
    ],
    App::PREPEND);

class YaDispatcher extends Dispatcher
{
    const PREFIX = 'YA';

    public function dispatch(CakeRequest $request, CakeResponse $response, $additionalParams = [])
    {
        try {
            $beforeEvent = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response', 'additionalParams'));
            $this->getEventManager()->dispatch($beforeEvent);

            $request = $beforeEvent->data['request'];
            if ($beforeEvent->result instanceof CakeResponse) {
                if (isset($request->params['return'])) {
                    return $beforeEvent->result->body();
                }
                $beforeEvent->result->send();

                return;
            }

            $controller = $this->_getController($request, $response);

            if (!($controller instanceof Controller)) {
                throw new MissingControllerException([
                    'class' => Inflector::camelize($request->params['controller']).'Controller',
                    'plugin' => empty($request->params['plugin']) ? null : Inflector::camelize($request->params['plugin']),
                ]);
            }

            $active = true;
            if (method_exists($controller, '_isActive')) {
                $active = $controller->_isActive();
            }

            if (!$active) {
                throw new InactiveControllerException();
            }

            $controller->name = preg_replace('/^'.self::PREFIX.'/', '', $controller->name);
            $controller->viewPath = preg_replace('/^'.self::PREFIX.'/', '', $controller->viewPath);
            $controller->modelClass = Inflector::singularize($controller->name);
            $controller->modelKey = Inflector::underscore($controller->modelClass);

            $response = $this->_invoke($controller, $request);
            if (isset($request->params['return'])) {
                return $response->body();
            }

            $afterEvent = new CakeEvent('Dispatcher.afterDispatch', $this, compact('request', 'response'));
            $this->getEventManager()->dispatch($afterEvent);
            $afterEvent->data['response']->send();
        } catch (Exception $e) {
            if (strpos(Inflector::camelize($request->params['controller']), self::PREFIX) !== false) {
                throw new MissingControllerException([
                    'class' => Inflector::camelize($request->params['controller']).'Controller',
                    'plugin' => empty($request->params['plugin']) ? null : Inflector::camelize($request->params['plugin']),
                ]);
            }

            $Dispatcher = new Dispatcher();
            $Dispatcher->dispatch(
                $request,
                $response,
                $additionalParams
            );
        }
    }

    protected function _loadController($request)
    {
        $pluginName = $pluginPath = $controller = null;
        if (!empty($request->params['plugin'])) {
            $pluginName = $controller = Inflector::camelize($request->params['plugin']);
            $pluginPath = $pluginName.'.';
        }
        if (!empty($request->params['controller'])) {
            $controller = Inflector::camelize($request->params['controller']);
        }

        if ($pluginPath.$controller) {
            $class = self::PREFIX.$controller.'Controller';
            if (strpos($class, self::PREFIX.self::PREFIX) !== false) {
                return false;
            }
            App::uses('AppController', 'Controller');
            App::uses($pluginName.'AppController', $pluginPath.'Controller');
            App::uses($class, $pluginPath.'Controller');
            if (class_exists($class)) {
                return $class;
            }
        }

        return parent::_loadController($request);
    }
}

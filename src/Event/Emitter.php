<?php

namespace EnderLab\Event;

class Emitter
{
    /**
     * Contient l'instance unique Emitter.
     *
     * @static
     *
     * @var Emitter
     */
    private static $instance;

    /**
     * Contient la liste des écouteurs.
     *
     * @var Listener[][]
     */
    private $listeners = [];

    /**
     * Permet de récupérer l'instance de l'émetteur (singleton).
     *
     * @return Emitter
     */
    public static function getInstance(): Emitter
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Permet de lancer un écènement.
     *
     * @param string $event
     * @param array  ...$args
     */
    public function emit(string $event, ...$args)
    {
        if (true === $this->hasListener($event)) {
            foreach ($this->listeners[$event] as $listener) {
                $listener->handle($args);

                if ($listener->getStopPropagation()) {
                    break;
                }
            }
        }
    }

    /**
     * Permet d'écouter un évènement.
     *
     * @param string   $event
     * @param callable $callable
     * @param int      $priority
     *
     * @return Listener
     */
    public function on(string $event, callable $callable, int $priority = 0): Listener
    {
        if (false === $this->hasListener($event)) {
            $this->listeners[$event] = [];
        }

        $this->callableExistsForEvent($event, $callable);
        $listener = new Listener($callable, $priority);
        $this->listeners[$event][] = $listener;
        $this->sortListeners($event);

        return $listener;
    }

    /**
     * Permet d'écouter un évènement une seule fois.
     *
     * @param string   $event
     * @param callable $callable
     * @param int      $priority
     *
     * @return Listener
     */
    public function once(string $event, callable $callable, int $priority = 0): Listener
    {
        return $this->on($event, $callable, $priority)->once();
    }

    /**
     * Permet d'ajouter un subscriber qui va écouter plusieurs évènements.
     *
     * @param ISubscriber $subscriber
     */
    public function addSubscriber(ISubscriber $subscriber)
    {
        $events = $subscriber->getEvents();

        foreach ($events as $event => $method) {
            [$callable, $priority, $once, $stopPropagation] = $this->buildSubscriberParams(
                (is_array($method) ? $method : [$method])
            );
            $listener = $this->on($event, [$subscriber, $callable], $priority);

            if (true === $once) {
                $listener->once();
            }

            if (true === $stopPropagation) {
                $listener->stopPropagation();
            }
        }
    }

    /**
     * Construit les paramètres à envoyer à la méthode "on".
     *
     * @param array $params
     *
     * @return array
     */
    private function buildSubscriberParams(array $params): array
    {
        $callable = null;
        $priority = 0;
        $once = false;
        $stopPropagation = false;

        switch (count($params)) {
            case 1:
                $callable = $params[0];
                break;
            case 2:
                $callable = $params[0];
                $priority = $params[1];
                break;
            case 3:
                $callable = $params[0];
                $priority = $params[1];
                $once = $params[2];
                break;
            case 4:
                $callable = $params[0];
                $priority = $params[1];
                $once = $params[2];
                $stopPropagation = $params[3];
                break;
        }

        return [$callable, $priority, $once, $stopPropagation];
    }

    /**
     * Test si un listener existe.
     *
     * @param string $event
     *
     * @return bool
     */
    private function hasListener(string $event): bool
    {
        return array_key_exists($event, $this->listeners);
    }

    /**
     * Tri la liste des listeners par ordre de priorité.
     *
     * @param string $event
     */
    private function sortListeners(string $event): void
    {
        uasort($this->listeners[$event], function ($a, $b) {
            return $a->getPriority() < $b->getPriority();
        });
    }

    /**
     * Test si un listener existe en double.
     *
     * @param string   $event
     * @param callable $callable
     *
     * @throws DoubleEventException
     *
     * @return bool
     */
    private function callableExistsForEvent(string $event, callable $callable): bool
    {
        foreach ($this->listeners[$event] as $listener) {
            if ($listener->getCallback() === $callable) {
                throw new DoubleEventException('Callback exist !');
            }
        }

        return false;
    }

    private function __construct()
    {
    }
}

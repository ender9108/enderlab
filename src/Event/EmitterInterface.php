<?php
/**
 * Created by PhpStorm.
 * User: axb
 * Date: 05/09/17
 * Time: 11:19.
 */

namespace EnderLab\Event;

interface EmitterInterface
{
    /**
     * Permet de lancer un écènement.
     *
     * @param string $event
     * @param array  ...$args
     */
    public function emit(string $event, ...$args);

    /**
     * Permet d'écouter un évènement.
     *
     * @param string   $event
     * @param callable $callable
     * @param int      $priority
     *
     * @return Listener
     */
    public function on(string $event, callable $callable, int $priority = 0): Listener;

    /**
     * Permet d'écouter un évènement une seule fois.
     *
     * @param string   $event
     * @param callable $callable
     * @param int      $priority
     *
     * @return Listener
     */
    public function once(string $event, callable $callable, int $priority = 0): Listener;

    /**
     * Permet d'ajouter un subscriber qui va écouter plusieurs évènements.
     *
     * @param Subscriber $subscriber
     */
    public function addSubscriber(Subscriber $subscriber);
}

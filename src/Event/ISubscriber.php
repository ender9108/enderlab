<?php

namespace EnderLab\Event;

interface ISubscriber
{
    /**
     * Return all events
     * [
     *   "{string EVENT_NAME}" => "{string METHOD}",
     *   "{string EVENT_NAME}" => ["{string METHOD}", {int PRIORITY}],
     *   "{string EVENT_NAME}" => ["{string METHOD}", {int PRIORITY}, {bool ONCE}],
     *   "{string EVENT_NAME}" => ["{string METHOD}", {int PRIORITY}, {bool ONCE}, {bool STOP_PROPAGATION}],
     * ].
     *
     * @return array
     */
    public function getEvents(): array;
}

<?php
namespace EnderLab\Event;

class Listener
{
  /**
   * @var callable
   */
  private $callback;

  /**
   * @var int
   */
  private $priority;

  /**
   * @var boolean
   */
  private $once = false;

  /**
   * @var boolean
   */
  private $calls = 0;

  /**
   * @var boolean
   */
  private $stopPropagation = false;

  /**
   * Constructeur de la classe
   *
   * @param callable $callback
   * @param int $priority
   */
  public function __construct(callable $callback, int $priority)
  {
    $this->callback = $callback;
    $this->priority = $priority;
  }

  /**
   * Execute listener callback
   *
   * @param array $args
   * @return mixed
   */
  public function handle(array $args)
  {
    if( true === $this->once && $this->calls > 0 )
    {
      return null;
    }

    $this->calls++;
    return call_user_func_array($this->callback, $args);
  }

  /**
   * Permet de stopper la propagation des évènement suivant
   *
   * @return Listener
   */
  public function stopPropagation(): Listener
  {
    $this->stopPropagation = true;
    return $this;
  }

  /**
   * Get listener priority
   *
   * @return int
   */
  public function getPriority()
  {
    return $this->priority;
  }

  /**
   * Get listener callback
   *
   * @return callable
   */
  public function getCallback()
  {
    return $this->callback;
  }

  /**
   * Get listener priority
   *
   * @return int
   */
  public function getStopPropagation()
  {
    return $this->stopPropagation;
  }

  /**
   * Permet d'indiquer que le listener ne peut être appelé qu'une fois
   *
   * @param bool $once
   * @return Listener
   */
  public function once(): Listener
  {
    $this->once = true;
    return $this;
  }
}
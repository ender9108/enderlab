<?php
namespace App;

class SubTest implements \EnderLab\Event\ISubscriber
{
  /**
   * Return all events
   * [
   *   "{string EVENT_NAME}" => "{string METHOD}",
   *   "{string EVENT_NAME}" => ["{string METHOD}", {int PRIORITY}],
   *   "{string EVENT_NAME}" => ["{string METHOD}", {int PRIORITY}, {bool ONCE}],
   *   "{string EVENT_NAME}" => ["{string METHOD}", {int PRIORITY}, {bool ONCE}, {bool STOP_PROPAGATION}],
   * ]
   *
   * @return array
   */
  public function getEvents(): array
  {
    return [
      'Comment.created' => 'onNewComment',
      'Comment.updated' => ['onUpdateComment', 0, false, true],
      'Comment.deleted' => ['onDeleteComment', 0, true],
      'Comment.readed'  => ['onReadComment', 0, true, true],
    ];
  }

  public function onNewComment(?array $args): void
  {
    print 'New comment '.$args['title'].' !!<br>';
  }

  public function onUpdateComment(): void
  {
    print 'Update comment !!<br>';
  }

  public function onDeleteComment(): void
  {
    print 'Delete comment !!<br>';
  }

  public function onReadComment(): void
  {
    print 'Read comment<br>';
  }
}
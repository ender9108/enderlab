<?php
use \EnderLab\Event\Emitter;

describe(Emitter::class, function() {
  it('should be a singleton', function() {
    $emitter = Emitter::getInstance();

    expect($emitter)->toBeAnInstanceOf(Emitter::class);
    expect($emitter)->toBeAnInstanceOf(Emitter::getInstance());
  });

  describe('::on', function(){
    it('should trigger the listened event', function() {
      $emitter = Emitter::getInstance();
      $calls = [];
      $emitter->on('Comment.created', function() use(&$calls) {
        $calls[] = 2;
      });
      expect(count($calls))->toBe(0);
      $emitter->emit('Comment.created');
      expect(count($calls))->toBe(1);
    });
  });
});
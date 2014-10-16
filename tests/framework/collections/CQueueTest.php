<?php

use Mindy\Base\Queue;

class CQueueTest extends CTestCase
{
    public function testConstruct()
    {
        $queue = new Queue();
        $this->assertEquals(array(), $queue->toArray());
        $queue = new Queue(array(1, 2, 3));
        $this->assertEquals(array(1, 2, 3), $queue->toArray());
    }

    public function testToArray()
    {
        $queue = new Queue(array(1, 2, 3));
        $this->assertEquals(array(1, 2, 3), $queue->toArray());
    }

    public function testCopyFrom()
    {
        $queue = new Queue(array(1, 2, 3));
        $data = array(4, 5, 6);
        $queue->copyFrom($data);
        $this->assertEquals(array(4, 5, 6), $queue->toArray());
    }

    public function testCanNotCopyFromNonTraversableTypes()
    {
        $queue = new Queue();
        $data = new stdClass();
        $this->setExpectedException('\Mindy\Exception\Exception');
        $queue->copyFrom($data);
    }

    public function testClear()
    {
        $queue = new Queue(array(1, 2, 3));
        $queue->clear();
        $this->assertEquals(array(), $queue->toArray());
    }

    public function testContains()
    {
        $queue = new Queue(array(1, 2, 3));
        $this->assertTrue($queue->contains(2));
        $this->assertFalse($queue->contains(4));
    }

    public function testPeek()
    {
        $queue = new Queue(array(1));
        $this->assertEquals(1, $queue->peek());
    }

    public function testCanNotPeekAnEmptyQueue()
    {
        $queue = new Queue();
        $this->setExpectedException('\Mindy\Exception\Exception');
        $item = $queue->peek();
    }

    public function testDequeue()
    {
        $queue = new Queue(array(1, 2, 3));
        $first = $queue->dequeue();
        $this->assertEquals(1, $first);
        $this->assertEquals(array(2, 3), $queue->toArray());
    }

    public function testCanNotDequeueAnEmptyQueue()
    {
        $queue = new Queue();
        $this->setExpectedException('\Mindy\Exception\Exception');
        $item = $queue->dequeue();
    }

    public function testEnqueue()
    {
        $queue = new Queue();
        $queue->enqueue(1);
        $this->assertEquals(array(1), $queue->toArray());
    }

    public function testGetIterator()
    {
        $queue = new Queue(array(1, 2));
        $this->assertInstanceOf('\Mindy\Base\QueueIterator', $queue->getIterator());
        $n = 0;
        $found = 0;
        foreach ($queue as $index => $item) {
            foreach ($queue as $a => $b) ; // test of iterator
            $n++;
            if ($index === 0 && $item === 1)
                $found++;
            if ($index === 1 && $item === 2)
                $found++;
        }
        $this->assertTrue($n == 2 && $found == 2);
    }

    public function testGetCount()
    {
        $queue = new Queue();
        $this->assertEquals(0, $queue->getCount());
        $queue = new Queue(array(1, 2, 3));
        $this->assertEquals(3, $queue->getCount());
    }

    public function testCountable()
    {
        $queue = new Queue();
        $this->assertEquals(0, count($queue));
        $queue = new Queue(array(1, 2, 3));
        $this->assertEquals(3, count($queue));
    }
}

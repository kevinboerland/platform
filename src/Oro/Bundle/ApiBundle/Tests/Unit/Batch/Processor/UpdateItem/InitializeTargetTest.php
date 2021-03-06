<?php

namespace Oro\Bundle\ApiBundle\Tests\Unit\Batch\Processor\UpdateItem;

use Oro\Bundle\ApiBundle\Batch\Processor\UpdateItem\InitializeTarget;
use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\ApiBundle\Provider\ConfigProvider;
use Oro\Bundle\ApiBundle\Provider\MetadataProvider;
use Oro\Component\ChainProcessor\ActionProcessorInterface;

class InitializeTargetTest extends BatchUpdateItemProcessorTestCase
{
    /** @var InitializeTarget */
    private $processor;

    protected function setUp()
    {
        parent::setUp();
        $this->processor = new InitializeTarget();
    }

    /**
     * @return Context
     */
    private function getTargetContext(): Context
    {
        return new Context(
            $this->createMock(ConfigProvider::class),
            $this->createMock(MetadataProvider::class)
        );
    }

    /**
     * @expectedException \Oro\Bundle\ApiBundle\Exception\RuntimeException
     * @expectedExceptionMessage The target processor is not defined.
     */
    public function testProcessWhenNoTargetProcessor()
    {
        $this->processor->process($this->context);
    }

    /**
     * @expectedException \Oro\Bundle\ApiBundle\Exception\RuntimeException
     * @expectedExceptionMessage The target context is not defined.
     */
    public function testProcessWhenNoTargetContext()
    {
        $this->context->setTargetProcessor($this->createMock(ActionProcessorInterface::class));
        $this->processor->process($this->context);
    }

    /**
     * @expectedException \Oro\Bundle\ApiBundle\Exception\RuntimeException
     * @expectedExceptionMessage The target last group is not defined.
     */
    public function testProcessWhenNoLastGroupInTargetContext()
    {
        $targetContext = $this->getTargetContext();

        $this->context->setTargetProcessor($this->createMock(ActionProcessorInterface::class));
        $this->context->setTargetContext($targetContext);
        $this->processor->process($this->context);
    }

    public function testProcessWhenNoErrorsOccurredWhenProcessingTargetProcessor()
    {
        $targetProcessor = $this->createMock(ActionProcessorInterface::class);

        $targetContext = $this->getTargetContext();
        $targetContext->setLastGroup('initialize');

        $targetProcessor->expects(self::once())
            ->method('process');

        $this->context->setTargetProcessor($targetProcessor);
        $this->context->setTargetContext($targetContext);
        $this->processor->process($this->context);

        self::assertFalse($this->context->hasErrors());
    }

    public function testProcessWhenSomeErrorsOccurredWhenProcessingTargetProcessor()
    {
        $targetProcessor = $this->createMock(ActionProcessorInterface::class);

        $targetContext = $this->getTargetContext();
        $targetContext->setLastGroup('initialize');

        $error = Error::create('some error');

        $targetProcessor->expects(self::once())
            ->method('process')
            ->willReturnCallback(function (Context $context) use ($error) {
                $context->addError($error);
            });

        $this->context->setTargetProcessor($targetProcessor);
        $this->context->setTargetContext($targetContext);
        $this->processor->process($this->context);

        self::assertFalse($targetContext->hasErrors());
        self::assertTrue($this->context->hasErrors());
        self::assertEquals([$error], $this->context->getErrors());
    }
}

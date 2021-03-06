<?php

namespace Oro\Bundle\ApiBundle\Form\EventListener;

use Oro\Bundle\ApiBundle\Form\FormUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * This listener is intended to
 * * reset value of a field bound to CompoundObjectType form type
 * * add mandatory value constraint violation for fields with "required" option is set to TRUE
 *   and that value does not exist in the submitted data
 * * add an entity processed by CompoundObjectType form type to the list of additional entities
 *   of API context within this form is processed
 */
class CompoundObjectListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'preSubmit',
            FormEvents::POST_SUBMIT => ['postSubmit', -250]
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $submittedData = $event->getData();
        if (null === $submittedData) {
            $submittedData = [];
            if ($form->getConfig()->getRequired()) {
                foreach ($form as $name => $child) {
                    $submittedData[$name] = null;
                    if ($child->isRequired()) {
                        $this->addRequiredFieldConstraintViolation($form, $name);
                    }
                }
            }
            $event->setData($submittedData);
        } elseif (\is_array($submittedData)) {
            /** @var FormInterface $child */
            foreach ($form as $name => $child) {
                if (!\array_key_exists($name, $submittedData) && $child->isRequired()) {
                    $this->addRequiredFieldConstraintViolation($form, $name);
                }
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $entity = $form->getData();
        if (null === $entity) {
            return;
        }

        $context = FormUtil::getApiContext($form);
        if (null !== $context) {
            $context->addAdditionalEntity($entity);
        }
    }

    /**
     * @param FormInterface $form
     * @param string        $fieldName
     */
    private function addRequiredFieldConstraintViolation(FormInterface $form, string $fieldName): void
    {
        FormUtil::addFormError($form, 'This value is mandatory.', $fieldName);
    }
}

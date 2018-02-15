<?php

namespace Oro\Bundle\OrganizationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class BusinessUnitType extends AbstractType
{
    const FORM_NAME = 'oro_business_unit';

    /** @var BusinessUnitManager */
    protected $businessUnitManager;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /**
     * @param BusinessUnitManager    $businessUnitManager
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function __construct(
        BusinessUnitManager $businessUnitManager,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->businessUnitManager = $businessUnitManager;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'label'    => 'oro.organization.businessunit.name.label',
                    'required' => true,
                ]
            )
            ->add(
                'parentBusinessUnit',
                'oro_type_business_unit_select_autocomplete',
                [
                    'required' => false,
                    'label' => 'oro.organization.businessunit.parent.label',
                    'autocomplete_alias' => 'business_units_owner_search_handler',
                    'empty_value' => 'oro.business_unit.form.none_business_user',
                    'configs' => [
                        'multiple' => false,
                        'component'   => 'tree-autocomplete',
                        'width'       => '400px',
                        'placeholder' => 'oro.dashboard.form.choose_business_unit',
                        'allowClear'  => true
                    ]
                ]
            )
            ->add(
                'phone',
                'text',
                [
                    'label'    => 'oro.organization.businessunit.phone.label',
                    'required' => false,
                ]
            )
            ->add(
                'website',
                'text',
                [
                    'label'    => 'oro.organization.businessunit.website.label',
                    'required' => false,
                ]
            )
            ->add(
                'email',
                'text',
                [
                    'label'    => 'oro.organization.businessunit.email.label',
                    'required' => false,
                ]
            )
            ->add(
                'fax',
                'text',
                [
                    'label'    => 'oro.organization.businessunit.fax.label',
                    'required' => false,
                ]
            )
            ->add(
                'appendUsers',
                'oro_entity_identifier',
                [
                    'class'    => 'OroUserBundle:User',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )
            ->add(
                'removeUsers',
                'oro_entity_identifier',
                [
                    'class'    => 'OroUserBundle:User',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'              => 'Oro\Bundle\OrganizationBundle\Entity\BusinessUnit',
                'ownership_disabled'      => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::FORM_NAME;
    }

    /**
     * Return current organization id
     *
     * @return int|null
     */
    protected function getOrganizationId()
    {
        return $this->tokenAccessor->getOrganizationId();
    }
}

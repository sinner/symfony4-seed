<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as FieldType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FormAbstract
 * @package EN\Api\AppBundle\Form
 */
abstract class FormAbstract extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'method' => Request::METHOD_POST,
                'translation_domain' => 'form',
                'csrf_protection' => false,
                'attr' => [
                    'data-is-form' => 1,
					'data-form-method' => Request::METHOD_POST
                ]
            ]
        );
    }

	/**
	 * Add token field.
	 *
	 * @param FormBuilderInterface $builder
     * @param string $token
	 *
	 * @return FormAbstract
	 */
	protected function addToken(FormBuilderInterface $builder, string $token): FormAbstract
	{
		$builder->add(
			'token',
			FieldType\HiddenType::class,
			[
				'attr' => [
					'value' => $token
				],
				'required' => true
			]
		);

		return $this;
	}

    /**
     * Returns an array of padded years.
     *
     * @param int $yearsBehind The number of years to return behind the current year.
     * @param int $yearsAhead The number of years to return ahead of the current year.
     *
     * @return array
     */
    protected function getPaddedYears(int $yearsBehind = 0, int $yearsAhead = 5): array
    {
        $currentYear = date('Y');

        $years = [];

        foreach(range($currentYear + $yearsBehind, $currentYear + $yearsAhead) as $year) {
            $years[] = $year;
        }

        return $years;
    }

    /**
     * Returns an array of padded months.
     *
     * @return array
     */
    protected function getPaddedMonths(): array
    {
        $months = [];

        foreach(range(1,12) as $month) {
            $months[] = str_pad($month, 2, '0', STR_PAD_LEFT);
        }

        return $months;
    }

    /**
     * Returns an array of padded days.
     *
     * @return array
     */
    protected function getPaddedDays(): array
    {
        $days = [];

        foreach(range(1,31) as $day) {
            $days[] = str_pad($day, 2, '0', STR_PAD_LEFT);
        }

        return $days;
    }

    /**
     * Returns an array of padded hours.
     *
     * @return array
     */
    protected function getPaddedHours(): array
    {
        $hours = [];

        foreach(range(0,23) as $hour) {
            $hours[] = str_pad($hour, 2, '0', STR_PAD_LEFT);
        }

        return $hours;
    }

    /**
     * Returns an array of padded minutes.
     *
     * @return array
     */
    protected function getPaddedMinutes(): array
    {
        $minutes = [];

        foreach(range(0,59) as $minute) {
            $minutes[] = str_pad($minute, 2, '0', STR_PAD_LEFT);
        }

        return $minutes;
    }
}

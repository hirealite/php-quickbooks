<?php

namespace Tests\Resources\NameList;

use PhpQuickbooks\Resources\NameList\Customer;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_customer()
    {
        $name = $this->faker->name;

        $customer = $this->quickbooks->customer()->create([
            'DisplayName' => $name,
        ]);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals($name, $customer->display_name);
    }

    /** @test */
    public function it_can_find_a_customer()
    {
        $name = $this->faker->name;

        $customer = $this->quickbooks->customer()->create([
            'DisplayName' => $name,
        ]);

        $customer = $this->quickbooks->customer()->find($customer->id);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals($name, $customer->display_name);
    }

    /** @test */
    public function it_can_update_a_customer()
    {
        $name1 = $this->faker->name;
        $name2 = $this->faker->name;

        // Create a customer
        $customer = $this->quickbooks->customer()->create([
            'display_name' => $name1,
        ]);

        $customer->update([
            'display_name' => $name2,
            'bill_addr'    => [
                'country_sub_division_code' => 'Suffolk',
            ],
        ]);

        $customer = $this->quickbooks->customer()->find($customer->id);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals($name2, $customer->display_name);
    }

    /** @test */
    public function it_can_return_nested_attributes()
    {
        $name = $this->faker->name;
        $street_address = $this->faker->streetName;

        $customer = $this->quickbooks->customer()->create([
            'DisplayName' => $name,
            'BillAddr'    => [
                'Line1'                  => $street_address,
                'City'                   => $this->faker->city,
                'Country'                => 'GB',
                'CountrySubDivisionCode' => 'Norfolk',
                'PostalCode'             => $this->faker->postcode,
            ],
        ]);

        $customer = $this->quickbooks->customer()->find($customer->id);
        $value = $customer->currency_ref->value;

        $this->assertTrue(is_string($value));
        $this->assertEquals($value, 'GBP');

        $this->assertEquals($street_address, $customer->bill_addr->line1);
        $this->assertEquals("Norfolk", $customer->bill_addr->country_sub_division_code);
    }
}

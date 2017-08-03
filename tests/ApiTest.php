<?php
declare(strict_types=1);

define( 'API_DEBUG_LEVEL', 1 );
define( 'API_DBHOST', 'localhost' );
define( 'API_DBNAME', 'octobattles' );
define( 'API_DBUSER', 'root' );
define( 'API_DBPASS', 'jpteurotux' );

use PHPUnit\Framework\TestCase;

/**
 * @covers Api
 */
final class ApiTest extends TestCase {
    /** @test */
    public function testCanAssociateEndpointsWithModels(): void
    {
        $endpoint_api_obj = new endpoint_Characters();

        var_dump($endpoint_api_obj -> model);

        $this->assertEquals(
            true,
            ( ! empty( $endpoint_api_obj -> model ) )
        );
    }

    /** @test */
    public function testCannotAssociateEndpointsWithModels(): void
    {

        $endpoint_api_obj = new endpoint_Types();

        var_dump($endpoint_api_obj -> model);

        $this->assertEquals(
            true,
            ( empty( $endpoint_api_obj -> model ) )
        );
    }

    /** @test */
    public function testCheckingValidRequestFormat(): void
    {

        $valid_format = 'json';
        $is_valid_format = Api::check_format( $valid_format );

        $this -> assertEquals(
            true,
            $is_valid_format
        );
    }

    /** @test */
    public function testCheckingInvalidRequestFormat(): void
    {

        $valid_format = 'abcd';
        $is_valid_format = Api::check_format( $valid_format );

        $this -> assertEquals(
            false,
            $is_valid_format
        );
    }

    /** @test */
    public function testCheckingValidRequestVersion(): void
    {

        $valid_version = 'v1.0';
        $is_valid_version = Api::check_version( $valid_version );

        $this -> assertEquals(
            true,
            $is_valid_version
        );
    }

    /** @test */
    public function testCheckingInvalidRequestVersion(): void
    {

        $valid_version = 'v2.0';
        $is_valid_version = Api::check_version( $valid_version );

        $this -> assertEquals(
            false,
            $is_valid_version
        );
    }
}
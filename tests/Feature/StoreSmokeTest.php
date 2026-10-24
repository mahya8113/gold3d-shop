<?php
namespace Tests\Feature;
use Tests\TestCase;
class StoreSmokeTest extends TestCase{public function test_home_page_is_served():void{$this->get('/')->assertOk();}}

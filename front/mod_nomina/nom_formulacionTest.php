function test_tipoConcepto() {
  // Test case 1: When value is 'sueldo_base'
  $this->assertEquals('hide', tipoConcepto('sueldo_base'));

  // Test case 2: When tipo_calculo is '6'
  $this->assertEquals('hide', tipoConcepto('some_value', '6'));

  // Test case 3: When tipo_calculo is not '6'
  $this->assertEquals('show', tipoConcepto('some_value', '5'));
}

// Run the tests
test_tipoConcepto();
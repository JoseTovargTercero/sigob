export const nom_dependencia_form = ({ resetInputFunction }) => {
  return `  <div id='modal-dependency' class='modal-window hide'>
      <div class='modal-box short slide-up-animation'>
        <header class='modal-box-header'>
          <h4>AÑADIR NUEVA DEPENDENCIA</h4>
          <button
            id='btn-close-dependency'
            type='button'
            class='btn btn-danger'
            aria-label='Close'
          >
            &times;
          </button>
        </header>

        <div class='modal-box-content'>
          <form id='employee-dependencia-form'>
            <div class='row mx-0'>
              <div class='col-sm'>
                <input
                  class=' form-control'
                  type='text'
                  name='dependencia'
                  placeholder='Nombre dependencia...'
                  id='dependencia'
                />
              </div>
              <div class='col-sm'>
                <input
                  type='number'
                  class=' form-control'
                  name='cod_dependencia-input'
                  id='cod_dependencia-input'
                  placeholder='Codigo de dependencia'
                />
              </div>
            </div>
          </form>
        </div>

        <div class='modal-box-footer'>
          <button class='btn btn-primary' id='dependency-save-btn'>
            GUARDAR DEPENDENCIA
          </button>
        </div>
      </div>
    </div>`
}

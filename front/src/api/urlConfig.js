function validateUrl() {
  let url = new URL(window.location.href)
  let protocol = url.protocol
  let host = url.host
  let pathname = url.pathname

  return `${protocol}//${host}/`
}

const isLocalhost = () => {
  return window.location.href.includes('localhost')
}

const config = {
  BASE_URL: validateUrl(),
  APP_NAME: isLocalhost() ? 'sigob/' : '',
  DIR: 'back/',
  MODULE_NAMES: {
    ENTES: 'modulo_entes/',
    GLOBAL: 'sistema_global/',
    FORMULACION: 'modulo_pl_formulacion/',
    EJECUCION: 'modulo_ejecucion_presupuestaria/',
  },
}

const APP_URL = `${config.BASE_URL}${config.APP_NAME}${config.DIR}`

export { APP_URL, config }

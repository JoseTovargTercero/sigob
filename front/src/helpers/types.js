const NOTIFICATIONS_TYPES = {
  delete: 'DELETE',
  send: 'SEND',
  done: 'DONE',
  fail: 'FAIL',
}

const ALERT_TYPES = {
  danger: 'danger',
  success: 'success',
  warning: 'warning',
  info: 'info',
  dark: 'dark',
  light: 'light',
  secondary: 'secondary',
  primary: 'primary',
}

const FRECUENCY_TYPES = {
  1: ['s1', 's2', 's3', 's4'],
  2: ['q1', 'q2'],
  3: ['unico'],
  4: ['unico'],
}

export { NOTIFICATIONS_TYPES, FRECUENCY_TYPES, ALERT_TYPES }

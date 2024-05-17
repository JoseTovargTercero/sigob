const regularExpressions = {
  TEXT: /^[A-Za-z0-9\sáéíóúÁÉÍÓÚüñÑ]+$/u,
  FLOAT: /^\d+(\.\d{1,2})?$/,
  NUMBER: /^\d+(\.\d{0,0})?$/,
}

export { regularExpressions }

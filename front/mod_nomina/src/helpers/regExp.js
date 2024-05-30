const regularExpressions = {
  TEXT: /^[A-Za-z0-9\sáéíóúÁÉÍÓÚüñÑ]+$/u,
  FLOAT: /^\d+(\.\d{1,2})?$/,
  NUMBER: /^\d+(\.\d{0,0})?$/,
  EMAIL: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/,
}

export { regularExpressions }

const regularExpressions = {
  TEXT: /^[A-Za-z0-9\sáéíóúÁÉÍÓÚüñÑ_\-.,]+$/u,
  FLOAT: /^\d+(\.\d{1,2})?$/,
  NUMBER: /^\d+(\.\d{0,0})?$/,
  EMAIL: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/,
  PASSWORD: /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).+/,
  CATEGORY: /^\d{1,2}-\d{1,2}-\d{1,2}-\d{1,2}$/,
  TASA: /^(?=.*[^0]\d*)(0\.(0*[1-9]\d*)?|[1-9]\d*(\.\d+)?)$/,
  PARTIDA: /^\d{3}\.\d{2}\.\d{2}\.\d{2}\.\d{4}$/,
}

export { regularExpressions }

const isFloat = (number) => {
  let regExp = /^\d+(\.\d{2,})?$/
  return number.match(regExp) ? true : false
}

export { isFloat }

function validateInput(fieldList = {}, e) {
  fieldList = {
    ...fieldList,
    [e.target.name]: convertStringOrNumber(e.target.value),
  }
  console.log(fieldList)
  return fieldList
}

const convertStringOrNumber = (string) =>
  isNaN(Number(string)) ? string : Number(string)

export { validateInput }

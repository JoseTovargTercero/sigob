import { getCategorias } from "../api/categorias.js";
import { getDependencias } from "../api/dependencias.js";
import {
  getBankData,
  getEmployeeByCedula,
  getEmployeeData,
  getJobData,
  getProfessionData,
  sendEmployeeData,
  updateRequestEmployeeData,
} from "../api/empleados.js";
import { consultarPartida, getPartidas } from "../api/partidas.js";
import { nom_categoria_form_card } from "../components/nom_categoria_form_card.js";
import { nomCorrectionAlert } from "../components/nom_correcion_alert.js";
import { nom_dependencia_form_card } from "../components/nom_dependencia_form_card.js";
import { employeeCard } from "../components/nom_empleado_card.js";
import {
  closeModal,
  confirmNotification,
  openModal,
  toastNotification,
  validateInput,
  validateModal,
} from "../helpers/helpers.js";
import { ALERT_TYPES, NOTIFICATIONS_TYPES } from "../helpers/types.js";
import {
  confirmDeleteEmployee,
  loadEmployeeTable,
  validateEmployeeTable,
} from "./empleadosTable.js";

let fieldList = {
  nombres: "",
  nacionalidad: "",
  cedula: 0,
  status: "",
  instruccion_academica: "",
  cod_cargo: "",
  fecha_ingreso: "",
  otros_anios: 0,
  hijos: 0,
  beca: 0,
  discapacidades: "",
  banco: "",
  cuenta_bancaria: "",
  // tipo_cuenta: 0,
  id_dependencia: "",
  id_categoria: "",
  id_partida: "",

  tipo_nomina: 0,
  correcion: 0,
  observacion: "",
};

let fieldListErrors = {
  nombres: {
    value: true,
    message: "Introducir un campo válido",
    type: "text",
  },
  nacionalidad: {
    value: true,
    message: "Introducir un campo válido",
    type: "text",
  },
  cedula: {
    value: true,
    message: "Introduzca cédula válida",
    type: "cedula",
  },
  status: {
    value: false,
    message: "Elija una opción",
    type: "text",
  },
  instruccion_academica: {
    value: true,
    message: "Elija una opción",
    type: "text",
  },
  cod_cargo: {
    value: true,
    message: "Elija un cargo",
    type: "number",
  },
  fecha_ingreso: {
    value: true,
    message: "Fecha inválida o mayor",
    type: "date",
  },
  otros_anios: {
    value: true,
    message: 'Introducir cantidad o "0"',
    type: "number2",
  },
  hijos: {
    value: true,
    message: 'Introducir cantidad o "0"',
    type: "number2",
  },
  beca: {
    value: true,
    message: 'Introducir cantidad o "0"',
    type: "number2",
  },
  banco: {
    value: true,
    message: "Elija un banco",
    type: "text",
  },
  cuenta_bancaria: {
    value: true,
    message: "Introducir N° de cuenta válido",
    type: "cuenta_bancaria",
  },
  discapacidades: {
    value: true,
    message: "Elija una opción",
    type: "number2",
  },
  // tipo_cuenta: {
  //   value: true,
  //   message: 'Elegir tipo de cuenta',
  //   type: 'number2',
  // },
  id_dependencia: {
    value: true,
    message: "Elegir una dependencia",
    type: "number",
  },
  id_categoria: {
    value: true,
    message: "Elegir una unidad",
    type: "number",
  },
  id_partida: {
    value: true,
    message: "Elegir una partida",
    type: "text",
  },
  tipo_nomina: {
    value: null,
    message: "Introducir un campo válido",
    type: "number2",
  },
  observacion: {
    value: null,
    message: "Introducir un campo válido",
    type: "text",
  },
};

let fieldListDependencias = {
  dependencia: "",
  "cod_dependencia-input": "",
};
let fieldListErrorsDependencias = {
  dependencia: {
    value: true,
    message: "No puede estar vacío",
    type: "text",
  },
  "cod_dependencia-input": {
    value: true,
    message: "No puede estar vacío",
    type: "number",
  },
};

const d = document;
const w = window;

let employeeId;

let partidas;

function validateEmployeeForm({
  employeeInputClass,
  employeeSelectClass,
  btnId,
  btnAddId,
  selectSearchInput,
  selectSearch,
}) {
  const formElement = d.getElementById("employee-form");
  if (!formElement) return;
  const btnElement = d.getElementById(btnId);
  const btnAddElement = d.getElementById(btnAddId);
  const btnDependencySave = d.getElementById("dependency-save-btn");

  const dependenciaFormElement = d.getElementById("employee-dependencia-form");

  const selectSearchInputElement = d.querySelectorAll(`.${selectSearchInput}`);

  const employeeInputElement = d.querySelectorAll(`.${employeeInputClass}`);
  const employeeSelectElement = d.querySelectorAll(`.${employeeSelectClass}`);

  let employeeInputElementCopy = [...employeeInputElement];
  let employeeSelectElementCopy = [...employeeSelectElement];

  const loadEmployeeData = async (id = false) => {
    let cargos = await getJobData();
    let profesiones = await getProfessionData();
    let dependencias = await getDependencias();
    let categorias = await getCategorias();
    partidas = await getPartidas();

    let bancos = await getBankData();
    insertOptions({ input: "cargo", data: cargos });
    insertOptions({ input: "instruccion_academica", data: profesiones });
    insertOptions({ input: "dependencias", data: dependencias.mappedData });
    insertOptions({ input: "categorias", data: categorias.mappedData });
    insertOptions({ input: "bancos", data: bancos });
    (() => {
      let partidasList = d.getElementById("partidas-list");
      partidasList.innerHTML = "";
      let options = partidas.fullInfo
        .map((option) => {
          return `<option value="${option.partida}">${option.descripcion}</option>`;
        })
        .join("");

      partidasList.innerHTML = options;
      return;
    })();

    // CÓDIGO PARA OBTENER EMPLEADO EN CASO DE EDITAR
    if (id) {
      // Obtener datos de empleado dada su ID
      let employeeData = await getEmployeeData(id);
      console.log(employeeData);
      let partidaSelect = employeeData.id_partida
        ? partidas.fullInfo.find(
            (partida) => partida.id == employeeData.id_partida
          ).partida
        : "";

      // SI EL EMPLEADO TIENE EL VERIFICADO EN 2, COLOCAR CORRECIÓN EN FORMULARCIÓN DE EDICIÓN

      if (employeeData.verificado === 2) {
        let correcionElement = d.getElementById("employee-correcion");
        if (correcionElement) correcionElement.remove();
        formElement.insertAdjacentHTML(
          "beforebegin",
          nomCorrectionAlert({
            message: employeeData.correcion,
            type: ALERT_TYPES.warning,
          })
        );
      }

      // Vacíar campo de dependencia no necesario
      delete employeeData.dependencia;

      employeeData.id = employeeData.id_empleado;
      if (employeeData.foto) {
        let preview = document.getElementById("empleado-foto");
        preview.src = `../../img/empleados/${employeeData.cedula}.jpg`;
      }
      employeeId = employeeData.id_empleado;

      console.log(employeeId);

      fieldList = employeeData;

      employeeSelectElementCopy.forEach((select) => {
        // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN SELECT
        if (employeeData[select.name] !== undefined)
          select.value = employeeData[select.name];

        validateInput({
          target: select,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[select.name].type,
        });
      });

      employeeInputElementCopy.forEach((input) => {
        // SI EL VALOR NO ES UNDEFINED COLOCAR VALOR EN INPUT
        if (input.name === "cedula") {
          input.setAttribute("disabled", "true");
        }

        if (employeeData[input.name] !== undefined)
          input.value = employeeData[input.name];

        if (input.name === "id_partida") {
          input.value = partidaSelect;
        }
        validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        });
      });

      // mostrarCodigoDependencia()

      // console.log(fieldList, fieldListErrors)
    } else {
      delete fieldList.id;
      delete fieldList.employeeId;
      d.getElementById("cedula").removeAttribute("disabled");
    }
  };

  formElement.addEventListener("submit", (e) => e.preventDefault());

  // dependenciaFormElement.addEventListener('submit', (e) => e.preventDefault())

  formElement.addEventListener("input", (e) => {
    if (e.target.id === "empleado-foto-input") {
      previewImage(e);
    }
    if (e.target.classList.contains(employeeInputClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      });
    }

    if (e.target.classList.contains(employeeSelectClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      });
      if (e.target.name === "id_dependencia") {
        // mostrarCodigoDependencia()
      }
    }
  });

  // dependenciaFormElement.addEventListener('input', (e) => {
  //   console.log(e.target.value)
  //   fieldListDependencias = validateInput({
  //     target: e.target,
  //     fieldList: fieldListDependencias,
  //     fieldListErrors: fieldListErrorsDependencias,
  //     type: fieldListErrorsDependencias[e.target.name].type,
  //   })

  //   console.log(fieldListDependencias)
  // })

  formElement.addEventListener("focusout", (e) => {
    if (e.target.name === "cedula") {
      getEmployeeByCedula({ cedula: e.target.value }).then((res) => {
        if (!res.status) {
          toastNotification({
            type: NOTIFICATIONS_TYPES.fail,
            message: res.mensaje,
          });
          // Resetear input si el status es falso
          e.target.value = "";
          fieldList = validateInput({
            target: e.target,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[e.target.name].type,
          });

          formElement.otros_anios.value = "";
        } else {
          // SI EL USUARIO EXISTE, SUMAR LOS OTROS AÑOS
          if (!res.otros_anios) {
            formElement.otros_anios.value = "";
            return;
          }

          toastNotification({
            type: NOTIFICATIONS_TYPES.done,
            message:
              "Existe registro de este empleado. Se actualizará el campo otros años laborales",
          });
          formElement.otros_anios.value =
            Number(formElement.otros_anios.value) + res.otros_anios;
        }
      });
    }

    if (e.target.name === "id_partida") {
      // if (e.target.value === '') return
      let partidaEncontrada = partidas.fullInfo.find(
        (partida) => partida.partida === e.target.value
      );

      if (!partidaEncontrada) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: `Partida "${e.target.value}" no se encuentra registrada`,
        });
        e.target.value = "";
      } else {
        toastNotification({
          type: NOTIFICATIONS_TYPES.done,
          message: `Partida "${e.target.value}" verificada  `,
        });
      }

      // consultarPartida({ informacion: { id: e.target.value } }).then((res) => {
      //   if (res.success) {
      //   } else {
      //     toastNotification({
      //       type: NOTIFICATIONS_TYPES.fail,
      //       message: res.error,
      //     })
      //     e.target.value = ''
      //   }
      // })
    }
    if (e.target.classList.contains(employeeInputClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      });
    }

    if (e.target.classList.contains(employeeSelectClass)) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      });
    }
    console.log(fieldList);
  });

  // selectSearchInputElement.forEach((input) => {
  //   const parentElement = input.parentNode
  //   const parentChildElement = parentElement.childNodes

  //   console.log(parentElement)
  //   parentElement.addEventListener('click', (e) => {
  //     activateSelect({ selectSearchId: `search-select-${input.name}` })
  //   })

  //   parentElement.addEventListener('focusout', (e) => {
  //     desactivateSelect({ selectSearchId: `search-select-${input.name}` })
  //   })
  // })

  d.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-delete")) {
      let fila = e.target.closest("tr");
      console.log(fila);
      confirmDeleteEmployee({
        id: e.target.dataset.id,
        row: fila,
      });
    }

    if (e.target.classList.contains("btn-view")) {
      employeeCard({
        id: e.target.dataset.id,
        elementToInsert: "employee-table-view",
      });
    }

    if (e.target.id === "btn-close-employee-card") {
      d.getElementById("modal-employee").remove();
    }

    if (e.target.classList.contains("btn-edit")) {
      loadEmployeeData(e.target.dataset.id);
      openModal({ modalId: "modal-employee-form" });
    }

    if (e.target.id === "btn-employee-form-open") {
      loadEmployeeData();
      openModal({ modalId: "modal-employee-form" });
    }
    if (e.target.id === "btn-employee-form-close") {
      closeModal({ modalId: "modal-employee-form" });
      formElement.reset();
      validateInput({
        type: "reset",
      });
      employeeSelectElementCopy.forEach((select) => {
        select.value = "";
      });

      employeeInputElementCopy.forEach((input) => {
        input.value = "";
      });

      employeeId = undefined;

      let preview = document.getElementById("empleado-foto");
      preview.src = `../src/assets/img/default.jpg`;
    }

    if (e.target.id === "add-dependency") {
      nom_dependencia_form_card({
        elementToInsert: "modal-employee-form",
        reloadSelect: loadDependencias,
      });
      openModal({ modalId: "modal-dependency" });
    }

    if (e.target.id === "add-category") {
      nom_categoria_form_card({
        elementToInsert: "modal-employee-form",
        reloadSelect: loadCategorias,
      });
    }

    if (e.target.id === "actualizar-opciones") {
      loadCategorias();
      loadDependencias();
      const numeroFinal = 0;
      let contador = 5;
      let intervalo;
      e.target.innerText = `ACTUALIZAR OPCIONES EN ${contador}`;
      e.target.setAttribute("disabled", true);

      if (contador >= numeroFinal) {
        intervalo = setInterval(function () {
          contador--;
          e.target.innerText = `ACTUALIZAR OPCIONES EN ${contador}`;
          if (contador === numeroFinal) {
            clearInterval(intervalo);
            e.target.removeAttribute("disabled");
            e.target.innerText = "ACTUALIZAR OPCIONES";
          }
        }, 1000); // Intervalo de 1 segundo (1000 milisegundos)
      }
    }

    // if (e.target === btnDependencySave) {
    //   console.log(
    //     dependenciaFormElement['cod_dependencia-input'].value,
    //     dependenciaFormElement.dependencia.value
    //   )
    //   let newDependency = {
    //     dependencia: dependenciaFormElement.dependencia.value,
    //     cod_dependencia: dependenciaFormElement['cod_dependencia-input'].value,
    //   }
    //   if (!fieldListDependencias.dependencia)
    //     return confirmNotification({
    //       type: NOTIFICATIONS_TYPES.fail,
    //       message: 'No se puede enviar una dependencia vacía',
    //     })

    //   if (Object.values(fieldListDependencias).some((el) => el.value)) {
    //     return confirmNotification({
    //       type: NOTIFICATIONS_TYPES.fail,
    //       message: 'Error al registrar dependencia',
    //     })
    //   }

    //   validateNewDependency({ newDependency })
    //   dependenciaFormElement.reset()
    //   fieldListDependencias.dependencia = ''
    //   fieldListDependencias['cod_dependencia-input'] = ''
    // }

    // ENVIAR DATOS

    if (e.target === btnElement) {
      // VALIDAR EI BECAS CURSADAS ES MAYOR A HIJOS

      employeeSelectElementCopy.forEach((input) => {
        fieldList = validateInput({
          target: input,
          fieldList,
          fieldListErrors,
          type: fieldListErrors[input.name].type,
        });
      });

      employeeInputElementCopy.forEach((input) => {
        if (fieldListErrors[input.name])
          fieldList = validateInput({
            target: input,
            fieldList,
            fieldListErrors,
            type: fieldListErrors[input.name].type,
          });
      });

      console.log(fieldList, fieldListErrors);

      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: "Complete todo el formulario antes de avanzar",
        });
      }

      let id_partida = partidas.fullInfo.find(
        (partida) => partida.partida === fieldList.id_partida
      ).id;
      fieldList.id_partida = id_partida;

      console.log(fieldList);

      if (fieldList.beca > fieldList.hijos) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: "Becas cursadas no puede ser mayor a la cantidad de hijos.",
        });
        return;
      }
      delete fieldList.correcion;

      // EDITAR EMPLEADO

      if (employeeId)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          successFunction: function () {
            sendEmployeeInformationRequest({ data: fieldList }).then((res) => {
              closeModal({ modalId: "modal-employee-form" });
              loadEmployeeData();
              loadEmployeeTable();
            });
          },
          message: "¿Desea actualizar la información de este empleado?",
        });

      // REGISTRAR EMPLEADO
      return confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: function () {
          sendEmployeeData({ data: fieldList }).then((res) => {
            loadEmployeeData();
            loadEmployeeTable();
            closeModal({ modalId: "modal-employee-form" });
          });
        },

        message: "¿Desea registrar este empleado?",
      });
    }
  });
}

async function sendEmployeeInformationRequest({ data }) {
  let employeeDataRequest = await getEmployeeData(employeeId),
    employeeData = employeeDataRequest;

  console.log(employeeData);
  let updateData = [];

  Object.entries(data).forEach((el) => {
    let propiedad = el[0];
    let valorAnterior =
      typeof employeeData[propiedad] !== "string"
        ? String(employeeData[propiedad])
        : employeeData[propiedad];

    let valorNuevo = el[1] !== "string" ? String(el[1]) : el[1];

    if (!valorNuevo) return;

    if (propiedad === "id" || propiedad === "id_empleado") return;

    if (valorNuevo !== valorAnterior) {
      console.log(
        propiedad,
        valorNuevo,
        valorAnterior,
        "Se actualiza: ",
        valorNuevo !== valorAnterior
      );
      updateData.push([
        Number(employeeId),
        propiedad,
        valorNuevo,
        valorAnterior,
      ]);
    }
  });
  console.log(updateData);
  if (updateData.length === 0) {
    toast_s("error", "No hay cambios para este empleado");
    return;
  }

  let result = await updateRequestEmployeeData({ data: updateData });
}

function insertOptions({ input, data }) {
  const selectElement = d.getElementById(`search-select-${input}`);
  selectElement.innerHTML = `<option value="">Elegir...</option>`;
  const fragment = d.createDocumentFragment();
  data.forEach((el) => {
    const option = d.createElement("option");
    option.setAttribute("value", el.id);
    option.textContent = el.name;
    fragment.appendChild(option);
  });

  selectElement.appendChild(fragment);
}

async function loadDependencias() {
  getDependencias().then((res) => {
    // dependenciasLaborales = res.fullInfo
    insertOptions({ input: "dependencias", data: res.mappedData });
  });

  //  mostrarCodigoDependencia()
}

async function loadCategorias() {
  getCategorias().then((res) => {
    // dependenciasLaborales = res.fullInfo
    insertOptions({ input: "categorias", data: res.mappedData });
  });
}

function previewImage(event) {
  let input = event.target;

  if (input.files === 0) return;

  let file = input.files[0];

  console.log(file);

  if (!["image/jpeg", "image/png"].includes(file.type)) {
    toastNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Tipo de archivo no admitido",
    });

    event.target.value = "";
    return;
  }

  if (file.size > 2 * 1024 * 1024) {
    // Validar el tamaño del archivo
    // Mostrar un mensaje de error si el archivo es demasiado grande
    toastNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Archivo demasiado grande",
    });
    return;
  }

  let reader = new FileReader();
  reader.onload = function (e) {
    console.log(e);

    let dataURL = reader.result;
    let preview = document.getElementById("empleado-foto");
    preview.style.display = "none";

    // Guardar estado de la url
    fieldList.foto = e.target.result;
    fieldList.tipo_foto = file.type.replace("image/", "");

    preview.src = dataURL;
    preview.style.display = "block"; // Mostrar la imagen una vez cargada
  };
  reader.readAsDataURL(file);
}

function clearImagePreview() {
  var preview = document.getElementById("empleado-foto");
  preview.src = "#"; // Establecer la src como "#" limpiará la imagen
  preview.style.display = "none"; // Ocultar la imagen
  document.getElementById("empleado-foto-input").value = ""; // Limpiar el valor del input file
}

function mostrarCodigoDependencia() {
  // console.log(dependenciasLaborales)
  const id_dependencia = d.getElementById("search-select-dependencias").value;
  const cod_dependenciaInput = d.getElementById("cod_dependencia");

  const dependenciaSeleccionada = dependenciasLaborales.find(
    (dep) => dep.id_dependencia == id_dependencia
  );
  console.log(dependenciaSeleccionada);
  if (dependenciaSeleccionada) {
    cod_dependenciaInput.value = dependenciaSeleccionada.cod_dependencia;
  } else {
    cod_dependenciaInput.value = ""; // Limpiar el input si no hay ninguna dependencia seleccionada
  }
}

const activateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.remove("hide");
};

const desactivateSelect = ({ selectSearchId }) => {
  d.getElementById(selectSearchId).classList.add("hide");
};

export { validateEmployeeForm };

import { eliminarTipoGasto, getGasto } from "../api/pre_gastos.js";
import {
  ejerciciosLista,
  validarEjercicioActual,
} from "../components/form_ejerciciosLista.js";
import { pre_gastos_form_card } from "../components/pre_gastos_form_card.js";
import { pre_gastosDetalles } from "../components/pre_gastosDetalles.js";
import { confirmNotification, toastNotification } from "../helpers/helpers.js";
import { NOTIFICATIONS_TYPES } from "../helpers/types.js";
import {
  deleteGasto,
  deleteTipoGasto,
  loadGastosTable,
  validateGastosTable,
  validateTiposGastosTable,
} from "./pre_gastosFuncionamientoTable.js";

const d = document;
export const validateGastosView = async () => {
  if (!document.getElementById("gastos-view")) return;

  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: "ejercicios-fiscales",
  });

  validateGastosTable({
    id_ejercicio: ejercicioFiscal ? ejercicioFiscal.id : null,
  });
  validateTiposGastosTable();

  console.log(ejercicioFiscal);

  d.addEventListener("click", async (e) => {
    if (e.target.id === "gastos-registrar") {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: "No hay ejercicio fiscal seleccionado",
        });
        return;
      }
      scroll(0, 0);

      pre_gastos_form_card({
        elementToInsert: "gastos-view",
        ejercicioFiscal: ejercicioFiscal,
        recargarEjercicio: async function () {
          let ejercicioFiscalElement = d.querySelector(
            `[data-ejercicioid="${ejercicioFiscal.id}"]`
          );
          ejercicioFiscal = await validarEjercicioActual({
            ejercicioTarget: ejercicioFiscalElement,
          });

          loadGastosTable({ id_ejercicio: ejercicioFiscal.id });
        },
      });
    }

    if (e.target.dataset.tableid) {
      mostrarTabla(e.target.dataset.tableid);
      d.querySelectorAll(".nav-link").forEach((el) => {
        el.classList.remove("active");
      });

      e.target.classList.add("active");
    }

    if (e.target.dataset.detallesid) {
      console.log("RESULTA:" + e.target.dataset.detallesid);
      scroll(0, 0);

      let formCard = d.getElementById("gastos-form-card");
      if (formCard) formCard.remove();

      let data = await getGasto(e.target.dataset.detallesid);
      pre_gastosDetalles({
        elementToInsert: "gastos-view",
        data,
        ejercicioFiscal: ejercicioFiscal,
        recargarEjercicio: async function () {
          let ejercicioFiscalElement = d.querySelector(
            `[data-ejercicioid="${ejercicioFiscal.id}"]`
          );
          ejercicioFiscal = await validarEjercicioActual({
            ejercicioTarget: ejercicioFiscalElement,
          });

          loadGastosTable({ id_ejercicio: ejercicioFiscal.id });
        },
      });
    }

    if (e.target.dataset.eliminarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: "¿Desea eliminar este tipo de gasto",
        successFunction: async function () {
          let res = await eliminarTipoGasto(e.target.dataset.eliminarid);
          if (res.success) {
            let row = e.target.closest("tr");
            deleteTipoGasto({ row });

            let formCard = d.getElementById("gastos-form-card");
            if (formCard) formCard.remove();
          }
        },
      });
    }

    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      let formCard = d.getElementById("gastos-form-card");
      if (formCard) formCard.remove();

      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      });
    }
  });
  return;
};

function mostrarTabla(tablaId) {
  let table1Id = "gastos-table";
  let table2Id = "tipos-gastos-table";

  let table1 = d.getElementById(`${table1Id}-container`);
  let table2 = d.getElementById(`${table2Id}-container`);

  if (tablaId === table1Id) {
    table1.classList.add("d-block");
    table1.classList.remove("d-none");
    table2.classList.add("d-none");
    table2.classList.remove("d-block");
  } else if (tablaId === table2Id) {
    table1.classList.add("d-none");
    table1.classList.remove("d-block");
    table2.classList.add("d-block");
    table2.classList.remove("d-none");
  }
}

// función para actualizar presupuesto según sea requerido

function actualizarPresupuesto() {
  let presupuesto = d.getElementById("presupuesto");
}

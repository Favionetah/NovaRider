<template>
  <div class="card border-0 shadow-sm bg-white rounded-3">
    <div class="card-body p-4">
      <!-- Sección Cabecera -->
      <div class="d-flex align-items-center gap-2 mb-4">
        <div class="bg-light rounded p-2 text-dark">
          <i class="bi bi-file-earmark-text fs-5"></i>
        </div>
        <div>
          <h6 class="fw-semibold text-dark mb-0 tracking-tight">Comprobante de Servicio / Venta</h6>
          <small class="text-muted" style="font-size: 0.75rem;">Paso 1: Datos de identificación del cliente</small>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
          <label class="form-label text-secondary small fw-medium mb-1.5">Nombre del Cliente</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-0 text-muted px-2.5"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control py-2 bg-light border-0" v-model="formulario.cliente" :disabled="!cajaAbierta" placeholder="Ej. Alex Zuazo" />
          </div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label text-secondary small fw-medium mb-1.5">Placa del Vehículo</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-0 text-muted px-2.5"><i class="bi bi-car-front"></i></span>
            <input type="text" class="form-control py-2 bg-light border-0 font-monospace text-uppercase" v-model="formulario.placa" :disabled="!cajaAbierta" placeholder="0000AAA" />
          </div>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label text-secondary small fw-medium mb-1.5">Motivo del Servicio / Notas generales</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light border-0 text-muted px-2.5"><i class="bi bi-wrench"></i></span>
          <input type="text" class="form-control py-2 bg-light border-0" v-model="formulario.concept" :disabled="!cajaAbierta" placeholder="Ej. Cambio de aceite 15W50 y revisión de pastillas" />
        </div>
      </div>

      <!-- Sección Detalle -->
      <div class="border-top border-light-subtle pt-4 mt-2">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="bg-light rounded p-2 text-dark" style="padding: 0.4rem 0.6rem !important;">
            <i class="bi bi-plus-circle small"></i>
          </div>
          <div>
            <span class="fw-semibold text-dark small d-block tracking-tight">Agregar Ítems al Carrito</span>
            <small class="text-muted" style="font-size: 0.72rem;">Paso 2: Adjunte repuestos o mano de obra</small>
          </div>
        </div>

        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-7">
            <input type="text" class="form-control form-control-sm py-2 bg-light border-0 px-3" v-model="itemTemporal.nombre" :disabled="!cajaAbierta" placeholder="Nombre del producto o servicio" />
          </div>
          <div class="col-12 col-md-3">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-0 text-muted font-monospace pe-0">Bs.</span>
              <input type="number" class="form-control py-2 bg-light border-0 font-monospace" v-model.number="itemTemporal.precio" :disabled="!cajaAbierta" placeholder="0.00" />
            </div>
          </div>
          <div class="col-12 col-md-2">
            <button class="btn btn-dark btn-sm w-100 py-2 fw-medium rounded-2 transition-all d-flex align-items-center justify-content-center gap-1" @click="agregarItem" :disabled="!cajaAbierta">
              <i class="bi bi-plus"></i> Añadir
            </button>
          </div>
        </div>
      </div>
      
      <div class="mt-4 pt-2.5 border-top border-light-subtle d-flex align-items-center gap-2 text-muted" style="font-size: 0.72rem;">
        <i class="bi bi-exclamation-circle text-secondary"></i>
        <span>Los datos de cabecera superiores son obligatorios antes de poder sumar transacciones al carro de cobros.</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({ cajaAbierta: { type: Boolean, default: false } })
const emit = defineEmits(['onAgregarItem'])

const formulario = ref({ cliente: '', placa: '', concept: '' })
const itemTemporal = ref({ nombre: '', precio: null })

const agregarItem = () => {
  if (!formulario.value.cliente.trim() || !formulario.value.placa.trim() || !formulario.value.concept.trim()) {
    alert('⚠️ Complete Nombre, Placa y Motivo del servicio en la sección superior primero.')
    return
  }
  if (!itemTemporal.value.nombre.trim() || !itemTemporal.value.precio || itemTemporal.value.precio <= 0) {
    return
  }

  emit('onAgregarItem', {
    cliente: formulario.value.cliente,
    placa: formulario.value.placa,
    concepto: formulario.value.concept,
    nombreItem: itemTemporal.value.nombre,
    precioItem: itemTemporal.value.precio
  })

  itemTemporal.value.nombre = ''
  itemTemporal.value.precio = null
}
</script>
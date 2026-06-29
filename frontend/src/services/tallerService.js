import api from './api'; 

const tallerService = {
  obtenerOrdenes() {
    return api.get('/ordenes'); // Llama al endpoint de Laravel para listar las órdenes reales
  },

  obtenerMotocicletas() {
    return api.get('/motocicletas');
  },

  obtenerMecanicos() {
    return api.get('/mecanicos');
  },

  crearOrden(payload) {
    return api.post('/ordenes', payload); 
  },
  
  // 🚀 ACTUALIZADO: Apunta al endpoint real para guardar en tlistasverificacion
  guardarChecklist(payload) {
    return api.post('/ordenes/guardar-verificacion', payload);
  },

  // 🚀 NUEVO MÉTODO: Actualizar datos de la orden mediante PUT
  actualizarOrden(id, payload) {
    return api.put(`/ordenes/${id}`, payload);
  },

  // 🚀 NUEVO MÉTODO: Eliminar orden (baja lógica) mediante DELETE
  eliminarOrden(id) {
    return api.delete(`/ordenes/${id}`);
  },

  // 🚀 AGREGADO: Método para cambiar el estado directamente en la base de datos
  cambiarEstadoOrden(id, estado) {
    return api.put(`/ordenes/${id}/cambiar-estado`, { estado });
  }
};

export default tallerService;
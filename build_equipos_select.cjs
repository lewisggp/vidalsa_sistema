const fs = require('fs');
const filepath = 'vidalsa_mobile/App.js';
let content = fs.readFileSync(filepath, 'utf-8');

// 1. Add states and logic inside PantallaEquipos
const stateLogicHook = `
  const [stats, setStats] = useState({ total: 0, inoperativos: 0, mantenimiento: 0 });

  // ─── LOGICA DE SELECCION MULTIPLE Y ASIGNACION MÓVIL ───
  const [equiposSelect, setEquiposSelect] = useState([]);
  const [modalAsignarVisible, setModalAsignarVisible] = useState(false);
  const [frentesData, setFrentesData] = useState([]);
  const [frenteDestinoAsignar, setFrenteDestinoAsignar] = useState('');
  const [guardandoAsignacion, setGuardandoAsignacion] = useState(false);

  useEffect(() => {
    (async () => {
      const f = await leerFrentesLocal();
      setFrentesData(f);
    })();
  }, []);

  const toggleSelectEquipo = (eq) => {
    setEquiposSelect(prev =>
      prev.find(e => e.id_equipo === eq.id_equipo)
        ? prev.filter(e => e.id_equipo !== eq.id_equipo)
        : [...prev, eq]
    );
  };

  const handleAsignarEquipos = async () => {
    if (!frenteDestinoAsignar) { Alert.alert('Atención', 'Selecciona el frente de destino'); return; }
    if (equiposSelect.length === 0) return;
    
    setGuardandoAsignacion(true);
    try {
      const db = await getDb();
      const fDestObj = frentesData.find(f => String(f.id_frente) === String(frenteDestinoAsignar));
      const fDestNombre = fDestObj ? fDestObj.nombre : '';

      for (const eq of equiposSelect) {
        await guardarMovPendiente({
          tipo: 'despacho', // Assign counts as a dispatch in offline
          id_equipo: eq.id_equipo,
          id_frente_dest: parseInt(frenteDestinoAsignar),
          detalle_ubi: ''
        });
        await db.runAsync(
          'UPDATE equipos SET frente = ? WHERE id_equipo = ?',
          [fDestNombre, eq.id_equipo]
        );
      }
      Alert.alert('✅ Asignados', \`\${equiposSelect.length} equipo(s) re-asignado(s) en la base local.\\n\\nRecuerda sincronizar más tarde.\`);
      setEquiposSelect([]);
      setModalAsignarVisible(false);
      setFrenteDestinoAsignar('');
      cargar(); // Update list locally
    } catch (e) {
      Alert.alert('Error', e.message);
    } finally {
      setGuardandoAsignacion(false);
    }
  };
`;

content = content.replace(
  `const [stats, setStats] = useState({ total: 0, inoperativos: 0, mantenimiento: 0 });`,
  stateLogicHook
);


// 2. Modify renderItem to handle selection
const renderItemOriginal = `const renderItem = ({ item }) => {
    const est = getEstado(item.estado);
    return (
      <View style={styles.equipoCard}>`;

const renderItemReplacement = `const renderItem = ({ item }) => {
    const est = getEstado(item.estado);
    const isSelected = equiposSelect.find(e => e.id_equipo === item.id_equipo);
    return (
      <TouchableOpacity 
         activeOpacity={0.8}
         delayLongPress={250}
         onLongPress={() => toggleSelectEquipo(item)}
         onPress={() => {
            if (equiposSelect.length > 0) toggleSelectEquipo(item);
            else { setEquipoSel(item); setModalVisible(true); }
         }}
         style={[styles.equipoCard, isSelected && { borderColor: '#3b82f6', borderWidth: 2, backgroundColor: '#eff6ff' }]}
      >
        {/* Checkmark Overly if selected */}
        {isSelected && (
           <View style={{position: 'absolute', top: 10, right: 10, zIndex: 10}}>
              <MaterialIcons name="check-circle" size={24} color="#3b82f6" />
           </View>
        )}`;

content = content.replace(renderItemOriginal, renderItemReplacement);

// Need to match the closing View for equipoCard: it was </View> but now it should be </TouchableOpacity>
// In the original, before the end of renderItem it says:
const renderItemCloseOriginal = `          </TouchableOpacity>
        </View>
      </View>
    );
  };`;
const renderItemCloseReplacement = `          </TouchableOpacity>
        </View>
      </TouchableOpacity>
    );
  };`;

content = content.replace(renderItemCloseOriginal, renderItemCloseReplacement);

// 3. Add Floating Bar and Asignar Modal before </SafeAreaView> in PantallaEquipos
const closingTagStr = `</SafeAreaView>
  );
}`;

const modalAndFloatingBar = `

      {/* ── BARRA FLOTANTE DE SELECCIÓN ── */}
      {equiposSelect.length > 0 && (
        <View style={{
           position: 'absolute', bottom: 20, left: 16, right: 16,
           backgroundColor: '#1e293b', borderRadius: 12, padding: 12,
           flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
           elevation: 10, shadowColor: '#000', shadowOpacity: 0.3, shadowRadius: 8, shadowOffset: {height: 4, width: 0}
        }}>
           <View style={{flexDirection: 'row', alignItems: 'center', gap: 10}}>
              <View style={{backgroundColor: '#3b82f6', width: 28, height: 28, borderRadius: 14, alignItems: 'center', justifyContent: 'center'}}>
                 <Text style={{color: '#fff', fontWeight: '800', fontSize: 13}}>{equiposSelect.length}</Text>
              </View>
              <Text style={{color: '#fff', fontWeight: '600', fontSize: 13}}>Seleccionados</Text>
           </View>
           
           <View style={{flexDirection: 'row', gap: 8}}>
              <TouchableOpacity onPress={() => setEquiposSelect([])} style={{padding: 8}}>
                 <Text style={{color: '#94a3b8', fontSize: 12, fontWeight: '600'}}>Limpiar</Text>
              </TouchableOpacity>
              <TouchableOpacity 
                 onPress={() => setModalAsignarVisible(true)} 
                 style={{backgroundColor: '#3b82f6', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 4}}
              >
                 <MaterialIcons name="local-shipping" size={16} color="#fff" />
                 <Text style={{color: '#fff', fontWeight: '700', fontSize: 13}}>Asignar</Text>
              </TouchableOpacity>
           </View>
        </View>
      )}

      {/* ── MODAL DE ASIGNACIÓN MASIVA ── */}
      <Modal visible={modalAsignarVisible} animationType="fade" transparent={true} onRequestClose={() => setModalAsignarVisible(false)}>
        <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'center', padding: 20 }}>
          <View style={{ backgroundColor: '#fff', borderRadius: 16, overflow: 'hidden' }}>
            <View style={{ backgroundColor: '#3b82f6', padding: 16, flexDirection: 'row', alignItems: 'center', gap: 10 }}>
               <MaterialIcons name="local-shipping" size={24} color="#fff" />
               <Text style={{ color: '#fff', fontSize: 16, fontWeight: '700' }}>Asignar {equiposSelect.length} equipo(s)</Text>
            </View>
            
            <View style={{ padding: 20 }}>
               <Text style={{ fontSize: 13, color: '#475569', marginBottom: 16 }}>
                 Selecciona a qué frente de trabajo deseas asignar los equipos seleccionados de manera offline:
               </Text>
               
               <Text style={{ fontSize: 11, fontWeight: '800', color: '#64748b', marginBottom: 6, textTransform: 'uppercase' }}>FRENTE DE DESTINO *</Text>
               
               {frentesData.length === 0 ? (
                 <Text style={{color: '#ef4444', fontSize: 12}}>No hay frentes guardados offline. Descarga datos primero.</Text>
               ) : (
                 <ScrollView style={{maxHeight: 200, marginBottom: 15, borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 8}}>
                   {frentesData.map(f => (
                      <TouchableOpacity 
                         key={f.id_frente}
                         style={{padding: 12, borderBottomWidth: 1, borderBottomColor: '#f1f5f9', backgroundColor: frenteDestinoAsignar === String(f.id_frente) ? '#eff6ff' : '#fff', flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'}}
                         onPress={() => setFrenteDestinoAsignar(String(f.id_frente))}
                      >
                         <Text style={{fontSize: 13, color: frenteDestinoAsignar === String(f.id_frente) ? '#1d4ed8' : '#334155', fontWeight: frenteDestinoAsignar === String(f.id_frente) ? '700' : '500'}}>{f.nombre}</Text>
                         {frenteDestinoAsignar === String(f.id_frente) && <MaterialIcons name="check" size={18} color="#1d4ed8" />}
                      </TouchableOpacity>
                   ))}
                 </ScrollView>
               )}

               <View style={{flexDirection: 'row', gap: 10, marginTop: 10}}>
                  <TouchableOpacity 
                     style={{flex: 1, paddingVertical: 12, borderRadius: 8, borderWidth: 1, borderColor: '#cbd5e1', alignItems: 'center'}}
                     onPress={() => setModalAsignarVisible(false)}
                     disabled={guardandoAsignacion}
                  >
                     <Text style={{color: '#475569', fontWeight: '600', fontSize: 13}}>Cancelar</Text>
                  </TouchableOpacity>
                  <TouchableOpacity 
                     style={{flex: 1, backgroundColor: '#3b82f6', paddingVertical: 12, borderRadius: 8, alignItems: 'center', opacity: (!frenteDestinoAsignar || guardandoAsignacion) ? 0.6 : 1}}
                     onPress={handleAsignarEquipos}
                     disabled={!frenteDestinoAsignar || guardandoAsignacion}
                  >
                     {guardandoAsignacion ? (
                        <ActivityIndicator color="#fff" size="small" />
                     ) : (
                        <Text style={{color: '#fff', fontWeight: '700', fontSize: 13}}>Confirmar</Text>
                     )}
                  </TouchableOpacity>
               </View>
            </View>
          </View>
        </View>
      </Modal>

</SafeAreaView>
  );
}`;

content = content.replace(closingTagStr, modalAndFloatingBar);

fs.writeFileSync(filepath, content, 'utf-8');
console.log("Aplicada la lógica de selección múltiple, barra flotante y asignación masiva en el APK.");

const fs = require('fs');
const filepath = 'vidalsa_mobile/App.js';
const content = fs.readFileSync(filepath, 'utf-8');

const new_component = `// ─── PANTALLA DE MOVILIZACIONES ───────────────────────────────────────────────
function PantallaMovilizaciones({ user, onOpenMenu }) {
  const [activeView, setActiveView] = useState('historial');
  const [frentes, setFrentes] = useState([]);
  const [equiposBusq, setEquiposBusq] = useState([]);
  const [buscarEq, setBuscarEq] = useState('');
  const [equiposSel, setEquiposSel] = useState([]);
  const [frenteDest, setFrenteDest] = useState('');
  const [frenteDestNombre, setFrenteDestNombre] = useState('');
  const [detUbi, setDetUbi] = useState('');
  const [tipoMov, setTipoMov] = useState('despacho');
  const [guardando, setGuardando] = useState(false);
  const [pendientes, setPendientes] = useState([]);
  const [sincronizando, setSincronizando] = useState(false);

  // Historial locales
  const [historial, setHistorial] = useState([]);
  const [cargandoHist, setCargandoHist] = useState(true);
  const [searchHistorial, setSearchHistorial] = useState('');

  const cargarHistorial = useCallback(async () => {
    setCargandoHist(true);
    try {
      const cached = await AsyncStorage.getItem('movilizaciones_historial');
      if (cached) setHistorial(JSON.parse(cached));

      const data = await api('GET', '/movilizaciones');
      if (Array.isArray(data)) {
        setHistorial(data);
        await AsyncStorage.setItem('movilizaciones_historial', JSON.stringify(data));
      }
    } catch (e) {
      console.log('Error history:', e.message);
    } finally {
      setCargandoHist(false);
    }
  }, []);

  useEffect(() => {
    (async () => {
      const f = await leerFrentesLocal();
      setFrentes(f);
      const p = await leerPendientes();
      setPendientes(p);
    })();
    cargarHistorial();
  }, [cargarHistorial]);

  const buscarEquipos = async (q) => {
    setBuscarEq(q);
    if (q.length < 2) { setEquiposBusq([]); return; }
    const data = await leerEquiposLocal(q);
    setEquiposBusq(data.slice(0, 10));
  };

  const toggleEquipo = (eq) => {
    setEquiposSel(prev =>
      prev.find(e => e.id_equipo === eq.id_equipo)
        ? prev.filter(e => e.id_equipo !== eq.id_equipo)
        : [...prev, eq]
    );
  };

  const registrarMovimiento = async () => {
    if (equiposSel.length === 0) { Alert.alert('Atención', 'Selecciona al menos un equipo.'); return; }
    if (!frenteDest) { Alert.alert('Atención', 'Selecciona el frente de destino.'); return; }
    setGuardando(true);
    try {
      if (tipoMov === 'despacho') {
        for (const eq of equiposSel) {
          await guardarMovPendiente({
            tipo: 'despacho',
            id_equipo: eq.id_equipo,
            id_frente_dest: parseInt(frenteDest),
            detalle_ubi: detUbi,
          });
          const database = await getDb();
          await database.runAsync(
            'UPDATE equipos SET frente = ? WHERE id_equipo = ?',
            [frenteDestNombre, eq.id_equipo]
          );
        }
      } else {
        await guardarMovPendiente({
          tipo: 'recepcion_directa',
          ids_equipos: equiposSel.map(e => e.id_equipo).join(','),
          id_frente_dest: parseInt(frenteDest),
          detalle_ubi: detUbi,
        });
        const database = await getDb();
        for (const eq of equiposSel) {
          await database.runAsync(
            'UPDATE equipos SET frente = ? WHERE id_equipo = ?',
            [frenteDestNombre, eq.id_equipo]
          );
        }
      }
      const p = await leerPendientes();
      setPendientes(p);
      Alert.alert('✅ Guardado', \`\${equiposSel.length} movimiento(s) guardado(s) en el teléfono.\\n\\nPresiona "Sincronizar" cuando tengas conexión.\`);
      setEquiposSel([]);
      setBuscarEq('');
      setEquiposBusq([]);
      setFrenteDest('');
      setFrenteDestNombre('');
      setDetUbi('');
      setActiveView('historial');
      setTimeout(cargarHistorial, 1000); // Recargar
    } catch (e) {
      Alert.alert('Error', 'No se pudo guardar: ' + e.message);
    } finally {
      setGuardando(false);
    }
  };

  const sincronizar = async () => {
    if (pendientes.length === 0) {
      Alert.alert('Sin pendientes', 'No hay movimientos pendientes de sincronizar.');
      return;
    }
    setSincronizando(true);
    let exitosos = 0;
    let fallidos = 0;
    try {
      for (const p of pendientes) {
        try {
          if (p.tipo_mov === 'despacho') {
            await api('POST', '/movilizaciones', {
              tipo: 'despacho',
              ID_EQUIPO: p.id_equipo,
              ID_FRENTE_DESTINO: p.id_frente_dest,
            });
          } else {
            const ids = p.ids_equipos.split(',').map(Number).filter(Boolean);
            await api('POST', '/movilizaciones', {
              tipo: 'recepcion_directa',
              ids,
              ID_FRENTE_DESTINO: p.id_frente_dest,
              DETALLE_UBICACION: p.detalle_ubi || '',
            });
          }
          await marcarSincronizado(p.id);
          exitosos++;
        } catch (_) {
          fallidos++;
        }
      }
      const nuevos = await leerPendientes();
      setPendientes(nuevos);
      if (exitosos > 0) cargarHistorial();
      Alert.alert(
        '🔄 Sincronización',
        \`✅ \${exitosos} movimiento(s) enviados al servidor.\\n\${fallidos > 0 ? \`⚠️ \${fallidos} fallaron (sin conexión).\` : ''}\`
      );
    } catch (e) {
      Alert.alert('Error', 'Error al sincronizar: ' + e.message);
    } finally {
      setSincronizando(false);
    }
  };

  const historialesFiltrados = useMemo(() => {
    if (!searchHistorial.trim()) return historial;
    const q = searchHistorial.toLowerCase();
    return historial.filter(h => 
      (h.equipo?.CODIGO_PATIO?.toLowerCase() || '').includes(q) ||
      (h.equipo?.SERIAL_CHASIS?.toLowerCase() || '').includes(q) ||
      (h.CODIGO_CONTROL && String(h.CODIGO_CONTROL).includes(q))
    );
  }, [historial, searchHistorial]);

  return (
    <SafeAreaView style={{ flex: 1, backgroundColor: '#fdfbfb' }}>
      <StatusBar barStyle="dark-content" backgroundColor="#ffffff" />
      <TopHeader onOpenMenu={onOpenMenu} />

      <Text style={[styles.dashboardTitle, { marginBottom: 15 }]}>Registro de{'\\n'}Movilizaciones</Text>

      {/* Selector de modo */}
      <View style={{ flexDirection: 'row', marginHorizontal: 16, marginBottom: 12, backgroundColor: '#e2e8f0', borderRadius: 10, padding: 4 }}>
        <TouchableOpacity style={{ flex: 1, backgroundColor: activeView === 'historial' ? '#fff' : 'transparent', borderRadius: 8, paddingVertical: 10, alignItems: 'center', shadowColor: activeView==='historial'?'#000':'transparent', shadowOpacity: 0.1, shadowRadius: 2, shadowOffset:{width:0,height:1} }} onPress={() => setActiveView('historial')}>
          <Text style={{ fontWeight: activeView === 'historial' ? '700' : '600', color: activeView === 'historial' ? '#00004d' : '#64748b', fontSize: 13 }}>Historial</Text>
        </TouchableOpacity>
        <TouchableOpacity style={{ flex: 1, backgroundColor: activeView === 'nuevo' ? '#fff' : 'transparent', borderRadius: 8, paddingVertical: 10, alignItems: 'center', shadowColor: activeView==='nuevo'?'#000':'transparent', shadowOpacity: 0.1, shadowRadius: 2, shadowOffset:{width:0,height:1} }} onPress={() => setActiveView('nuevo')}>
          <Text style={{ fontWeight: activeView === 'nuevo' ? '700' : '600', color: activeView === 'nuevo' ? '#00004d' : '#64748b', fontSize: 13 }}>Nueva Recepción (+)</Text>
        </TouchableOpacity>
      </View>

      <View style={{ paddingHorizontal: 16, paddingBottom: 6, flexDirection: 'row', justifyContent: 'flex-end' }}>
        {pendientes.length > 0 && (
          <TouchableOpacity
            style={[styles.btnSync, sincronizando && { opacity: 0.6 }, { backgroundColor: '#f59e0b', paddingHorizontal: 15, paddingVertical: 10, borderRadius: 10, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4 }]}
            onPress={sincronizar}
            disabled={sincronizando}
          >
            {sincronizando
              ? <ActivityIndicator color={C.white} size="small" />
              : <Text style={[styles.btnSyncText, { fontSize: 13 }]}>⬆ Sincronizar ({pendientes.length})</Text>
            }
          </TouchableOpacity>
        )}
      </View>

      <ScrollView contentContainerStyle={{ padding: 16 }}>
        {activeView === 'historial' ? (
          <View>
             {/* Barra de Filtros */}
             <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: '#fbfcfd', borderRadius: 12, paddingHorizontal: 15, height: 48, borderWidth: 1, borderColor: '#cbd5e0', marginBottom: 16 }}>
                <MaterialIcons name="search" size={20} color="#94a3b8" />
                <TextInput style={{ flex: 1, marginLeft: 10, fontSize: 13, color: '#1e293b' }} placeholder="Buscar control, equipo, serial..." placeholderTextColor="#94a3b8" value={searchHistorial} onChangeText={setSearchHistorial} />
                {searchHistorial ? <TouchableOpacity onPress={() => setSearchHistorial('')}><MaterialIcons name="close" size={18} color="#94a3b8" /></TouchableOpacity> : null}
             </View>

             <View style={{ flexDirection: 'row', gap: 10, marginBottom: 16 }}>
                <View style={{ flex: 1, borderWidth: 1, borderColor: '#cbd5e0', borderRadius: 8, height: 42, paddingHorizontal: 12, justifyContent: 'center', backgroundColor: '#fbfcfd' }}>
                  <Text style={{ fontSize: 13, color: '#64748b' }}>Filtro Tipo ▼</Text>
                </View>
                <View style={{ flex: 1, borderWidth: 1, borderColor: '#cbd5e0', borderRadius: 8, height: 42, paddingHorizontal: 12, justifyContent: 'center', backgroundColor: '#fbfcfd' }}>
                  <Text style={{ fontSize: 13, color: '#64748b' }}>Filtro Frente ▼</Text>
                </View>
             </View>

             {/* Indicador de carga */}
             {cargandoHist && historial.length === 0 ? (
               <ActivityIndicator size="large" color="#00004d" style={{ marginTop: 40 }} />
             ) : historialesFiltrados.length === 0 ? (
               <View style={{ alignItems: 'center', marginTop: 40, opacity: 0.5 }}>
                   <MaterialIcons name="inbox" size={48} color="#94a3b8" />
                   <Text style={{ color: '#64748b', marginTop: 10 }}>No hay movilizaciones.</Text>
               </View>
             ) : (
               <View>
                 <Text style={{ fontSize: 12, color: '#64748b', fontWeight: '700', marginBottom: 12, textTransform: 'uppercase' }}>ULTIMOS REGISTROS</Text>
                 {historialesFiltrados.map((h, i) => (
                    <View key={h.ID_MOVILIZACION || i} style={{ backgroundColor: '#fff', borderRadius: 12, padding: 15, marginBottom: 15, borderWidth: 1, borderColor: '#e2e8f0', shadowColor: '#000', shadowOffset: {width: 0, height: 2}, shadowOpacity: 0.05, shadowRadius: 4, elevation: 2 }}>
                      {/* Equipo Row */}
                      <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 12 }}>
                        <View style={{ width: 45, height: 45, borderRadius: 8, backgroundColor: '#f1f5f9', justifyContent: 'center', alignItems: 'center', marginRight: 12, borderWidth: 1, borderColor: '#f1f5f9' }}>
                          <MaterialIcons name="local-shipping" size={24} color="#94a3b8" />
                        </View>
                        <View style={{ flex: 1 }}>
                          <Text style={{ fontSize: 13, color: '#718096', fontWeight: '700', textTransform: 'uppercase' }}>{h.equipo?.TIPO || 'N/A'}</Text>
                          <Text style={{ color: '#4a5568', fontSize: 13 }}><Text style={{ fontWeight: '700' }}>S: </Text>{h.equipo?.SERIAL_CHASIS || 'S/S'}</Text>
                          <Text style={{ color: '#0ea5e9', fontSize: 13 }}><Text style={{ fontWeight: '700' }}>P: </Text>{h.equipo?.PLACA || 'S/P'}</Text>
                          <Text style={{ color: '#1e293b', fontSize: 13, fontWeight: '700' }}>ID: {h.equipo?.CODIGO_PATIO || 'N/D'}</Text>
                        </View>
                        <View style={{ alignItems: 'flex-end', justifyContent: 'flex-start' }}>
                           {h.CODIGO_CONTROL ? (
                             <Text style={{ fontWeight: '800', color: '#1e293b', fontSize: 13 }}>MV-{String(h.CODIGO_CONTROL).padStart(5, '0')}</Text>
                           ) : (
                             <View style={{ backgroundColor: '#e0e7ff', paddingHorizontal: 8, paddingVertical: 2, borderRadius: 10 }}>
                               <Text style={{ color: '#3730a3', fontSize: 11, fontWeight: '700' }}>R.D.</Text>
                             </View>
                           )}
                           <View style={{ marginTop: 6, alignItems: 'center' }}>
                             {h.ESTADO_MVO === 'TRANSITO' ? (
                               <Text style={{ color: '#ef4444', fontSize: 12, fontWeight: '800' }}>TRÁNSITO</Text>
                             ) : (
                               <View style={{ backgroundColor: '#dbeafe', borderWidth: 1, borderColor: '#93c5fd', paddingHorizontal: 6, paddingVertical: 4, borderRadius: 6, flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                                 <MaterialIcons name="done-all" size={12} color="#1e40af" />
                                 <Text style={{ color: '#1e40af', fontSize: 9, fontWeight: '700' }}>COMPLETADO</Text>
                               </View>
                             )}
                           </View>
                        </View>
                      </View>
                      
                      {/* Trayecto Row */}
                      <View style={{ backgroundColor: '#f8fafc', borderRadius: 10, padding: 12, marginBottom: 12, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 10 }}>
                        <View style={{ flex: 1, alignItems: 'center' }}>
                          <Text style={{ fontSize: 10, color: '#64748b', fontWeight: '800', textTransform: 'uppercase', marginBottom: 2 }}>Origen</Text>
                          <Text style={{ fontWeight: '600', color: '#4a5568', fontSize: 12, textAlign: 'center' }}>{h.frente_origen?.NOMBRE_FRENTE || 'Sin Origen'}</Text>
                        </View>
                        <MaterialIcons name="east" size={18} color="#cbd5e0" />
                        <View style={{ flex: 1, alignItems: 'center' }}>
                          <Text style={{ fontSize: 10, color: '#0067b1', fontWeight: '800', textTransform: 'uppercase', marginBottom: 2 }}>Destino</Text>
                          <Text style={{ fontWeight: '700', color: '#00004d', fontSize: 12, textAlign: 'center' }}>{h.frente_destino?.NOMBRE_FRENTE || 'Sin Destino'}</Text>
                        </View>
                      </View>

                      {/* RECEPCION DIRECTA */}
                      {h.TIPO_MOVIMIENTO === 'RECEPCION_DIRECTA' && (
                          <View style={{ alignItems: 'center', marginBottom: 12, marginTop: -6 }}>
                              <View style={{ backgroundColor: '#e0e7ff', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12, flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                                  <MaterialIcons name="input" size={12} color="#3730a3" />
                                  <Text style={{ color: '#3730a3', fontSize: 10, fontWeight: '700' }}>RECEPCIÓN DIRECTA</Text>
                              </View>
                          </View>
                      )}
                      
                      {/* Fechas Row */}
                      <View style={{ flexDirection: 'row', justifyContent: 'space-between', borderTopWidth: 1, borderTopColor: '#f1f5f9', paddingTop: 10 }}>
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                           <MaterialIcons name="logout" size={14} color="#ef4444" />
                           <Text style={{ fontSize: 12, color: '#334155', fontWeight: '600' }}>{h.FECHA_DESPACHO ? new Date(h.FECHA_DESPACHO).toLocaleDateString('es-VE') : '--'}</Text>
                        </View>
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                           <MaterialIcons name="login" size={14} color="#10b981" />
                           <Text style={{ fontSize: 12, color: '#334155', fontWeight: '600' }}>{h.FECHA_RECEPCION ? new Date(h.FECHA_RECEPCION).toLocaleDateString('es-VE') : '--'}</Text>
                        </View>
                      </View>
                    </View>
                 ))}
               </View>
             )}
          </View>
        ) : (
          <View>
            <Text style={styles.sectionTitle}>Tipo de Movimiento</Text>
            <View style={{ flexDirection: 'row', gap: 8, marginBottom: 16 }}>
              {['despacho', 'recepcion'].map(t => (
                <TouchableOpacity key={t} style={[styles.tipoBtn, tipoMov === t && styles.tipoBtnActive]} onPress={() => setTipoMov(t)}>
                  <Text style={[styles.tipoBtnText, tipoMov === t && styles.tipoBtnActiveText]}>
                    {t === 'despacho' ? '🚛 Despacho' : '📥 Recepción Directa'}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>

            <Text style={styles.label}>Buscar Equipo (código, placa, serie)</Text>
            <TextInput
              style={styles.input}
              placeholder="Ej: RET-001 o ABC-123"
              placeholderTextColor={C.textSec}
              value={buscarEq}
              onChangeText={buscarEquipos}
            />

            {equiposBusq.map(eq => {
              const sel = equiposSel.find(e => e.id_equipo === eq.id_equipo);
              return (
                <TouchableOpacity key={eq.id_equipo} style={[styles.equipoBusqItem, sel && styles.equipoBusqItemSel]} onPress={() => toggleEquipo(eq)}>
                  <Text style={[styles.equipoBusqText, sel && { color: C.white }]}>
                    {sel ? '✓ ' : ''}{eq.codigo_patio || eq.serial_chasis} · {eq.marca} {eq.modelo}
                  </Text>
                  <Text style={{ fontSize: 11, color: sel ? '#bfdbfe' : C.textSec }}>{eq.frente || 'Sin Frente'}</Text>
                </TouchableOpacity>
              );
            })}

            {equiposSel.length > 0 && (
              <View style={styles.seleccionadosBox}>
                <Text style={styles.seleccionadosTitle}>✅ {equiposSel.length} equipo(s) seleccionado(s):</Text>
                {equiposSel.map(e => <Text key={e.id_equipo} style={styles.seleccionadoItem}>• {e.codigo_patio || e.serial_chasis}</Text>)}
              </View>
            )}

            <Text style={styles.label}>Frente de Destino</Text>
            {frentes.length === 0 ? (
              <Text style={{ color: C.textSec, fontSize: 13, marginBottom: 12 }}>
                ⚠️ No hay frentes guardados. Descarga los datos primero.
              </Text>
            ) : (
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 12 }}>
                {frentes.map(f => (
                  <TouchableOpacity
                    key={f.id_frente}
                    style={[styles.frenteTag, frenteDest === String(f.id_frente) && styles.frenteTagActive]}
                    onPress={() => { setFrenteDest(String(f.id_frente)); setFrenteDestNombre(f.nombre); }}
                  >
                    <Text style={[styles.frenteTagText, frenteDest === String(f.id_frente) && { color: C.white }]}>
                      {f.nombre}
                    </Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>
            )}

            {tipoMov === 'recepcion' && (
              <>
                <Text style={styles.label}>Detalle de Ubicación (opcional)</Text>
                <TextInput style={styles.input} placeholder="Ej: Área de Mantenimiento" placeholderTextColor={C.textSec} value={detUbi} onChangeText={setDetUbi} />
              </>
            )}

            <TouchableOpacity
              style={[styles.btnPrimary, { marginTop: 8 }, guardando && { opacity: 0.6 }]}
              onPress={registrarMovimiento}
              disabled={guardando}
            >
              {guardando
                ? <ActivityIndicator color={C.white} />
                : <Text style={styles.btnPrimaryText}>💾 GUARDAR EN TELÉFONO</Text>
              }
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}
`;

const start_str = "// ─── PANTALLA DE MOVILIZACIONES ───────────────────────────────────────────────";
const end_str = "// ─── APP PRINCIPAL ────────────────────────────────────────────────────────────";

const start_idx = content.indexOf(start_str);
const end_idx = content.indexOf(end_str);

if (start_idx !== -1 && end_idx !== -1) {
    const updated = content.substring(0, start_idx) + new_component + '\n\n' + content.substring(end_idx);
    fs.writeFileSync(filepath, updated, 'utf-8');
    console.log("Reemplazo exitoso.");
} else {
    console.log("No se encontraron los delimitadores.");
}

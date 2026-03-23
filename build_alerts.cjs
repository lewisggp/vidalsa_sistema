const fs = require('fs');
const filepath = 'vidalsa_mobile/App.js';
let content = fs.readFileSync(filepath, 'utf-8');

// The new alert logic
const newAlertLogic = `// ─── SISTEMA DE ALERTAS MODERNAS ───
const AlertEmitter = {
  listener: null,
  emit: (title, message, type='info', buttons=null) => {
     if (AlertEmitter.listener) {
        AlertEmitter.listener({ title, message, type, buttons, visible: true });
     } else {
        Alert.alert(title, message, buttons);
     }
  }
};

export const showModernAlert = (title, message, type='info', buttons=null) => {
  AlertEmitter.emit(title, message, type, buttons);
};

function ModernAlertModal() {
  const [data, setData] = useState({ visible: false, title: '', message: '', type: 'info', buttons: null });

  useEffect(() => {
    AlertEmitter.listener = (d) => setData(d);
    return () => { AlertEmitter.listener = null; };
  }, []);

  if (!data.visible) return null;

  const close = () => setData(prev => ({ ...prev, visible: false }));

  const isSuccess = data.type === 'success' || data.title.includes('✅') || data.title.toLowerCase().includes('guardado') || data.title.toLowerCase().includes('exitosa') || data.title.toLowerCase().includes('asignados');
  const isError = data.type === 'error' || data.title.includes('❌') || data.title.toLowerCase().includes('error');
  const isWarning = data.type === 'warning' || data.title.toLowerCase().includes('atención') || data.title.toLowerCase().includes('cerrar sesión');
  
  const iconName = isSuccess ? 'check-circle' : (isError ? 'error' : (isWarning ? 'warning' : 'info'));
  const iconColor = isSuccess ? '#10b981' : (isError ? '#ef4444' : (isWarning ? '#f59e0b' : '#3b82f6'));
  const bgColor = isSuccess ? '#ecfdf5' : (isError ? '#fef2f2' : (isWarning ? '#fffbeb' : '#eff6ff'));

  // Default button if no buttons array given
  const renderButtons = () => {
     if (!data.buttons || data.buttons.length === 0) {
        return (
           <TouchableOpacity onPress={close} style={{ backgroundColor: iconColor, width: '100%', paddingVertical: 14, borderRadius: 10, alignItems: 'center' }}>
              <Text style={{ color: '#fff', fontSize: 15, fontWeight: '800' }}>OK</Text>
           </TouchableOpacity>
        );
     }
     return (
        <View style={{ flexDirection: 'row', gap: 10, width: '100%' }}>
           {data.buttons.map((btn, i) => {
              const isCancel = btn.style === 'cancel' || btn.text.toLowerCase() === 'cancelar';
              const isDestructive = btn.style === 'destructive' || btn.text.toLowerCase() === 'salir';
              
              const btnBgColor = isCancel ? '#f1f5f9' : (isDestructive ? '#ef4444' : iconColor);
              const btnTextColor = isCancel ? '#475569' : '#ffffff';
              const btnBorder = isCancel ? { borderWidth: 1, borderColor: '#cbd5e1' } : {};
              
              return (
                 <TouchableOpacity 
                    key={i} 
                    style={[btnBorder, { flex: 1, backgroundColor: btnBgColor, paddingVertical: 14, borderRadius: 10, alignItems: 'center' }]}
                    onPress={() => {
                       close();
                       if (btn.onPress) setTimeout(btn.onPress, 300); // give time to closing animation
                    }}
                 >
                    <Text style={{ color: btnTextColor, fontSize: 14, fontWeight: '800' }}>{btn.text}</Text>
                 </TouchableOpacity>
              );
           })}
        </View>
     );
  };

  return (
    <Modal visible={data.visible} transparent={true} animationType="fade" onRequestClose={close}>
      <View style={{ flex: 1, backgroundColor: 'rgba(15,23,42,0.65)', justifyContent: 'center', alignItems: 'center', padding: 24 }}>
        <View style={{ width: '100%', maxWidth: 360, backgroundColor: '#ffffff', borderRadius: 20, overflow: 'hidden', shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.25, shadowRadius: 20, elevation: 15 }}>
          
          <View style={{ alignItems: 'center', padding: 28, paddingBottom: 15 }}>
            <View style={{ width: 72, height: 72, borderRadius: 36, backgroundColor: bgColor, justifyContent: 'center', alignItems: 'center', marginBottom: 20 }}>
              <MaterialIcons name={iconName} size={40} color={iconColor} />
            </View>
            <Text style={{ fontSize: 20, fontWeight: '900', color: '#1e293b', marginBottom: 12, textAlign: 'center', textTransform: 'uppercase', letterSpacing: 0.5 }}>{data.title}</Text>
            <Text style={{ fontSize: 14, color: '#64748b', textAlign: 'center', lineHeight: 22 }}>{data.message}</Text>
          </View>
          
          <View style={{ padding: 24, paddingTop: 10 }}>
            {renderButtons()}
          </View>

        </View>
      </View>
    </Modal>
  );
}

// ─── FIN ALERTA MODERNA ───`;

// Substitute 'export default function App() {' to include ModernAlertModal at the end of the return
if (!content.includes('function ModernAlertModal')) {
    const importMark = 'import React, { useState, useEffect, useCallback, useMemo } from "react";';
    content = content.replace(importMark, importMark + '\n\n' + newAlertLogic);
    
    // Add inside default App():
    const appReturnSafe = '</SafeAreaProvider>';
    content = content.replace(appReturnSafe, '  <ModernAlertModal />\n    </SafeAreaProvider>');

    // Replace all Alert.alert( occurrences
    // We cannot just string replace Alert.alert because it looks like Alert.alert("Title", "Message")
    // Let's replace 'Alert.alert' with 'showModernAlert' everywhere
    content = content.replace(/Alert\.alert/g, 'showModernAlert');
}

fs.writeFileSync(filepath, content, 'utf-8');
console.log("Sistema de Alertas Moderno instalado y Alert.alert reemplazado.");

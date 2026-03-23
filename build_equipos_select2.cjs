const fs = require('fs');
const filepath = 'vidalsa_mobile/App.js';
let content = fs.readFileSync(filepath, 'utf-8');

const anchorButtonAdd = `<TouchableOpacity 
                 onPress={() => setModalAsignarVisible(true)} 
                 style={{backgroundColor: '#3b82f6', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 4}}
              >
                 <MaterialIcons name="local-shipping" size={16} color="#fff" />
                 <Text style={{color: '#fff', fontWeight: '700', fontSize: 13}}>Asignar</Text>
              </TouchableOpacity>`;

const anchorButtonReplace = `<TouchableOpacity 
                 onPress={() => setModalAnclajesVisible(true)} 
                 style={{backgroundColor: '#10b981', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 4}}
              >
                 <MaterialIcons name="anchor" size={16} color="#fff" />
                 <Text style={{color: '#fff', fontWeight: '700', fontSize: 13}}>Anclar</Text>
              </TouchableOpacity>
              <TouchableOpacity 
                 onPress={() => setModalAsignarVisible(true)} 
                 style={{backgroundColor: '#3b82f6', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, flexDirection: 'row', alignItems: 'center', gap: 4}}
              >
                 <MaterialIcons name="local-shipping" size={16} color="#fff" />
                 <Text style={{color: '#fff', fontWeight: '700', fontSize: 13}}>Asignar</Text>
              </TouchableOpacity>`;

if(content.includes(anchorButtonAdd)) {
    content = content.replace(anchorButtonAdd, anchorButtonReplace);
    fs.writeFileSync(filepath, content, 'utf-8');
    console.log("Botón de Anclar agregado a la barra flotante.");
} else {
    console.log("No se encontro el boton de asignar original para reemplazar.");
}

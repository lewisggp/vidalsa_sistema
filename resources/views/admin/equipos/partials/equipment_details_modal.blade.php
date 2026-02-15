<!-- Improved Details Modal -->
<div id="detailsModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 900px; padding: 0; border-radius: 16px; overflow: hidden; background: #f8fafc;">
        <!-- Modal Header -->
        <div style="background: var(--maquinaria-dark-blue); padding: 20px 25px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div>
                    <h2 id="modal_equipo_title" style="margin: 0; font-size: 20px; font-weight: 700;">Detalles del Equipo</h2>
                    <p id="modal_equipo_subtitle" style="margin: 5px 0 0 0; opacity: 0.8; font-size: 13px;"></p>
                </div>
                <a id="modal_gps_btn" href="#" target="_blank" style="display: none; background: #10b981; color: white; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; align-items: center; gap: 6px; transition: 0.2s; margin-right: 20px;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    <i class="material-icons" style="font-size: 18px;">gps_fixed</i>
                    GPS
                </a>
            </div>
            <button type="button" onclick="closeDetailsModal(event)" style="background: rgba(255,255,255,0.1); border: none; color: white; cursor: default; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <i class="material-icons">close</i>
            </button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 25px; max-height: 80vh; overflow-y: auto;">

            <!-- Vertical Accordion Layout -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                
                <details name="equipment_accordion" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <summary style="padding: 15px 20px; font-weight: 700; color: #059669; display: flex; align-items: center; gap: 10px; background: #f0fdf4; list-style: none;">
                        <i class="material-icons" style="font-size: 20px; color: #059669;">description</i> 
                        <span style="color: #1e293b;">Documentación Legal y Soportes</span>
                    </summary>
                    <div style="padding: 20px; border-top: 1px solid #e2e8f0;">
                        <div style="display: flex; flex-direction: column; gap: 15px; font-size: 14px;">
                        
                            <div class="detail-row-basic" style="display: flex; flex-direction: column; align-items: flex-start; gap: 0;">
                                <span style="color: #64748b; font-weight: 500; margin-bottom: 2px;">Titular del Registro</span>
                                <strong id="d_titular" style="color: #1e293b; font-size: 14px; line-height: 1.3; width: 100%; word-wrap: break-word;"></strong>
                            </div>

                            <!-- Placa -->
                            <div class="detail-row-basic">
                                <span style="color: #64748b; font-weight: 500;">Placa Identificadora</span>
                                <strong id="d_placa" style="color: var(--maquinaria-blue); font-size: 15px; letter-spacing: 0.5px;"></strong>
                            </div>

                            <!-- Documento Propiedad (With Button) -->
                            <div class="detail-row-doc">
                                <span style="color: #64748b; font-weight: 500;">Nro. Documento</span>
                                <strong id="d_nro_doc" style="color: #1e293b;"></strong>
                                <div id="d_btn_propiedad"></div>
                            </div>

                            <!-- Seguro -->
                            <div class="detail-row-doc">
                                <span style="color: #64748b; font-weight: 500;">Póliza de Seguro</span>
                                <div>
                                    <!-- Removed Insurance Name Display -->
                                    <small id="d_venc_seguro" style="color: #ef4444; font-weight: 600; font-size: 13px;"></small>
                                </div>
                                <div id="d_btn_poliza"></div>
                            </div>

                            <!-- ROTC -->
                            <div class="detail-row-doc">
                                <span style="color: #64748b; font-weight: 500;">Registro ROTC</span>
                                <strong id="d_fecha_rotc" style="color: #1e293b;"></strong>
                                <div id="d_btn_rotc"></div>
                            </div>

                            <!-- RACDA -->
                            <div class="detail-row-doc">
                                <span style="color: #64748b; font-weight: 500;">Registro RACDA</span>
                                <strong id="d_fecha_racda" style="color: #1e293b;"></strong>
                                <div id="d_btn_racda"></div>
                            </div>

                            <!-- Documento Adicional -->
                            <div class="detail-row-doc">
                                <span style="color: #64748b; font-weight: 500;">Documento Adicional</span>
                                <span></span>
                                <div id="d_btn_adicional"></div>
                            </div>

                        </div>
                    </div>
                </details>

                <!-- Section 2: General Information -->
                <details name="equipment_accordion" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                    <summary style="padding: 15px 20px; font-weight: 700; color: var(--maquinaria-blue); display: flex; align-items: center; gap: 10px; background: #f8fafc; list-style: none;">
                        <i class="material-icons" style="font-size: 20px; color: var(--maquinaria-blue);">info</i> 
                        <span style="color: #1e293b;">Información General</span>
                    </summary>
                    <div style="padding: 20px; border-top: 1px solid #e2e8f0;">
                        <div style="display: flex; flex-direction: column; gap: 15px; font-size: 14px;">
                            
                            <!-- Año -->
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Año de Fabricación:</span>
                                <strong id="d_anio" style="color: #1e293b;"></strong>
                            </div>

                            <!-- Categoría -->
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Categoría de Flota:</span>
                                <strong id="d_categoria" style="color: #1e293b;"></strong>
                            </div>

                            <!-- Motor Serial -->
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Serial de Motor:</span>
                                <strong id="d_motor_serial" style="color: #1e293b;"></strong>
                            </div>

                            <!-- Combustible -->
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Tipo de Combustible:</span>
                                <strong id="d_combustible" style="color: #1e293b;"></strong>
                            </div>

                            <!-- Consumo -->
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Consumo Promedio:</span>
                                <strong id="d_consumo" style="color: #1e293b;"></strong>
                            </div>

                        </div>
                    </div>
                </details>
            </div>

    </div>
</div>
</div>

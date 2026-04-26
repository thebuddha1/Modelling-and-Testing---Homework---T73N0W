(function(){
    const routes = window.appRoutes || { favoritesIndex: '/favorites', favoritesStore: '/favorites' };
    const csrf = (window.appConfig && window.appConfig.csrfToken) || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const budapest = [47.4979, 19.0402];
    const map = L.map('map').setView(budapest, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let tempMarker = null;
    let selectionMode = false;
    let friendsToShareTo = [];
    let currentDetailsPlace = null; 

    const instruction = document.getElementById('instruction');
    const addBtn = document.getElementById('add-fav');
    const shareBtn = document.getElementById('share-fav');
    const detailsBtn = document.getElementById('details-fav');
    const shareMsg = document.getElementById('share-msg');
    const favForm = document.getElementById('favorite-form');
    const saveBtn = document.getElementById('save-fav');
    const cancelBtn = document.getElementById('cancel-fav');
    const nameInput = document.getElementById('fav-name');
    const latInput = document.getElementById('fav-lat');
    const lngInput = document.getElementById('fav-lng');

    const shareModalShare = document.getElementById('share-modal-share');
    const shareModalBackdrop = document.getElementById('share-modal-backdrop');
    const shareModalClose = document.getElementById('share-modal-close');
    const shareModalCancel = document.getElementById('share-modal-cancel');
    const friendsListEl = document.getElementById('friends-list');
    
    const detailsModalBackdrop = document.getElementById('details-modal-backdrop');
    const detailsModalClose = document.getElementById('details-modal-close');
    const detailsDescription = document.getElementById('details-description');
    const detailsError = document.getElementById('details-error');
    const detailsEditBtn = document.getElementById('details-edit-btn');
    const detailsSaveBtn = document.getElementById('details-save-btn');
    const detailsCancelBtn = document.getElementById('details-cancel-btn');

    function setSelectionMode(on){
        selectionMode = on;
        if(on){
            if(instruction){
                instruction.textContent = 'Kattintson a térképre egy hely kiválasztásához (kattintás vagy dupla kattintás).';
                instruction.style.display = 'block';
            }
            addBtn.disabled = true;
            // hide share button while selecting a new location
            if(shareBtn) shareBtn.style.display = 'none';
            if(detailsBtn) detailsBtn.style.display = 'none';
        } else {
            if(instruction){
                instruction.textContent = 'Kattintson a gombra a hely kiválasztásához.';
                instruction.style.display = 'none';
            }
            addBtn.disabled = false;
        }
    }

    addBtn.addEventListener('click', function(){ setSelectionMode(true); });

    function onMapSelect(e){
        if(!selectionMode) return;
        const {lat, lng} = e.latlng;
        if(tempMarker) map.removeLayer(tempMarker);
        tempMarker = L.marker([lat, lng]).addTo(map);
        latInput.value = lat;
        lngInput.value = lng;
        favForm.style.display = 'block';
        nameInput.focus();
    }

    map.on('click', onMapSelect);
    map.on('dblclick', onMapSelect);

    cancelBtn.addEventListener('click', function(){
        if(tempMarker) map.removeLayer(tempMarker);
        tempMarker = null;
        favForm.style.display = 'none';
        nameInput.value = '';
        const err = document.getElementById('fav-error');
        if(err){ err.style.display = 'none'; err.textContent = ''; }
        setSelectionMode(false);
    });

    const markers = {}; // id => marker
    let selectedFavId = null; // currently selected favorite (from list or marker)

    function openShareModal(){
        if(!shareModalBackdrop) return;
        renderFriendsList();
        shareModalBackdrop.classList.add('show');
    }

    function closeShareModal(){
        if(!shareModalBackdrop) return;
        shareModalBackdrop.classList.remove('show');
    }

    function renderFriendsList(){
        if(!friendsListEl) return;
        friendsListEl.innerHTML = '';
        fetch('/friends/list', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(friends => {
            if (!Array.isArray(friends) || friends.length === 0) {
                friendsListEl.innerHTML = '<div style="color:#64748b">Nincs barátod.</div>';
                return;
            }
            friends.forEach(friend => {
                const div = document.createElement('div');
                div.className = 'friend-row';

                // Avatar
                const img = document.createElement('img');
                img.className = 'avatar';
                img.src = friend.avatar || '/images/mock_pfp.png';
                console.log('Friend avatar URL: ', img.src);
                img.alt = friend.name;
                div.appendChild(img);

                // Selection logic
                div.addEventListener('click', function() {
                    const idx = friendsToShareTo.findIndex(f => f.id === friend.id);
                    if (idx === -1) {
                        friendsToShareTo.push(friend);
                        div.classList.add('selected');
                    } else {
                        friendsToShareTo.splice(idx, 1);
                        div.classList.remove('selected');
                    }
                });

                // Name
                const name = document.createElement('span');
                name.className = 'friend-name';
                name.textContent = friend.name;
                div.appendChild(name);

                friendsListEl.appendChild(div);
            });
        })
        .catch(() => {
            friendsListEl.innerHTML = '<div style="color:#b91c1c">Hiba történt a barátok lekérésekor.</div>';
        });
    }

    async function doShareToFriend(friend, fav){
        if(!fav || !fav.lat || !fav.lng) return;

        // close modal after sharing
        closeShareModal();
        // hide share button as sharing completed
        selectedFavId = null; 
        if(shareBtn) shareBtn.style.display = 'none';
        if(detailsBtn) detailsBtn.style.display = 'none';
        setTimeout(()=>{ if(shareMsg) shareMsg.style.display = 'none'; }, 3000);
    }

    function renderFavoritesList(items){
        const list = document.getElementById('favorites-list');
        list.innerHTML = '';
        items.forEach(f => {
            // create list item
            const li = document.createElement('li');
            li.setAttribute('data-id', f.id);
            li.style.display = 'flex';
            li.style.alignItems = 'center';
            li.style.justifyContent = 'space-between';
            li.style.padding = '6px 8px';
            li.style.borderBottom = '1px solid #e6edf3';

            const nameSpan = document.createElement('span');
            nameSpan.textContent = f.name;
            nameSpan.style.cursor = 'pointer';
            nameSpan.style.flex = '1';

            // inline edit input (hidden by default)
            const editInput = document.createElement('input');
            editInput.type = 'text';
            editInput.value = f.name;
            editInput.style.display = 'none';
            editInput.style.flex = '1';
            editInput.style.padding = '6px';

            const itemError = document.createElement('div');
            itemError.style.display = 'none';
            itemError.style.color = '#b91c1c';
            itemError.style.fontSize = '0.9rem';
            itemError.style.marginTop = '6px';
            itemError.style.lineHeight = '1.2';

            nameSpan.addEventListener('click', function(){
                const marker = markers[f.id];
                if(marker){
                    map.flyTo(marker.getLatLng(), 16, {animate:true});
                    if (marker.openPopup) marker.openPopup();
                }
                // select this favorite and show share button for it
                selectedFavId = f.id;
                if(shareBtn){
                    shareBtn.style.display = 'block';
                    shareBtn.dataset.favId = f.id;
                    shareBtn.dataset.favName = f.name;
                    shareBtn.dataset.favLat = f.lat;
                    shareBtn.dataset.favLng = f.lng;
                    if(shareMsg){ shareMsg.style.display = 'none'; shareMsg.textContent = ''; }
                }
                if(detailsBtn){
                    detailsBtn.style.display = 'block';
                    detailsBtn.dataset.favId = f.id;
                    detailsBtn.dataset.favName = f.name;
                    detailsBtn.dataset.favDescription = f.description || '';
                    detailsBtn.dataset.isCreator = f.is_creator ? 'true' : 'false';
                }
            });

            const actions = document.createElement('div');
            actions.style.display = 'flex';
            actions.style.gap = '6px';
            actions.style.alignItems = 'center';

            const editBtn = document.createElement('button');
            editBtn.title = 'Szerkesztés';
            editBtn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i>';
            editBtn.style.border = 'none';
            editBtn.style.background = 'transparent';
            editBtn.style.cursor = 'pointer';
            editBtn.style.color = '#1e293b';

            editBtn.addEventListener('click', function(e){
                e.stopPropagation();
                // switch to edit mode
                nameSpan.style.display = 'none';
                editInput.style.display = 'inline-block';
                editInput.value = f.name;
                editInput.focus();
                itemError.style.display = 'none';
                itemError.textContent = '';
            });

            // handle enter to save, escape to cancel
            editInput.addEventListener('keydown', async function(e){
                if(e.key === 'Escape'){
                    editInput.style.display = 'none';
                    nameSpan.style.display = 'inline';
                    itemError.style.display = 'none';
                    itemError.textContent = '';
                    return;
                }
                if(e.key === 'Enter'){
                    const newName = editInput.value.trim();
                    if(!newName){
                        itemError.textContent = 'Adj meg egy rövid nevet a helynek.';
                        itemError.style.display = 'block';
                        return;
                    }
                    if(newName === f.name){
                        // user reverted to original name — clear any inline validation message
                        itemError.style.display = 'none';
                        itemError.textContent = '';
                        editInput.style.display = 'none';
                        nameSpan.style.display = 'inline';
                        return;
                    }
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try{
                        const res = await fetch(routes.favoritesIndex + '/' + f.id, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ name: newName })
                        });
                        if(!res.ok){
                            const err = await res.json().catch(()=>null);
                            if(err && err.errors && err.errors.name && err.errors.name.length){
                                itemError.textContent = err.errors.name[0];
                                itemError.style.display = 'block';
                                return;
                            }
                            console.error('Update failed', err);
                            itemError.textContent = (err && err.message) || 'Hiba a frissítés közben.';
                            itemError.style.display = 'block';
                            return;
                        }
                        // clear any previous inline error immediately
                        itemError.style.display = 'none';
                        itemError.textContent = '';
                        editInput.style.display = 'none';
                        nameSpan.style.display = 'inline';
                        // reload whole list so markers and list stay consistent
                        await loadFavorites();
                    }catch(err){ console.error(err); itemError.textContent = 'Hálózati hiba.'; itemError.style.display = 'block'; }
                }
            });

            // delete button (icon only)
            const delBtn = document.createElement('button');
            delBtn.title = 'Törlés';
            delBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
            delBtn.style.border = 'none';
            delBtn.style.background = 'transparent';
            delBtn.style.cursor = 'pointer';
            delBtn.style.color = '#ef4444';

                delBtn.addEventListener('click', async function(e){
                    e.stopPropagation();
                    if(!confirm('Biztosan törlöd ezt a kedvenc helyet?')) return;
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try{
                        const res = await fetch(routes.favoritesIndex + '/' + f.id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            }
                        });
                        if(res.status !== 204){
                            const err = await res.json().catch(()=>null);
                            console.error('Delete failed', err);
                            alert((err && err.message) || 'Hiba a törlés közben.');
                            return;
                        }
                        // reload favorites to ensure markers/list are in sync
                        await loadFavorites();
                    }catch(err){ console.error(err); alert('Hálózati hiba.'); }
                });

            actions.appendChild(editBtn);
            actions.appendChild(delBtn);

            const leftCol = document.createElement('div');
            leftCol.style.display = 'flex';
            leftCol.style.flexDirection = 'column';
            leftCol.style.flex = '1';
            leftCol.style.marginRight = '8px';

            const topRow = document.createElement('div');
            topRow.style.display = 'flex';
            topRow.style.alignItems = 'center';
            topRow.style.gap = '8px';

            nameSpan.style.margin = '0';
            nameSpan.style.flex = '1';
            editInput.style.minWidth = '120px';
            editInput.style.flex = '1';

            topRow.appendChild(nameSpan);
            topRow.appendChild(editInput);

            leftCol.appendChild(topRow);
            leftCol.appendChild(itemError);

            li.appendChild(leftCol);
            li.appendChild(actions);
            list.appendChild(li);
        });
    }

    async function loadFavorites(){
        try{
            const res = await fetch(routes.favoritesIndex);
            if(!res.ok) return;
            const data = await res.json();
            // clear existing markers
            Object.keys(markers).forEach(k => { if(markers[k]) map.removeLayer(markers[k]); delete markers[k]; });
            data.forEach(f => {
                // Create orange icon for shared places
                let markerIcon = null;
                if(f.shared_by_id){
                    markerIcon = L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                }
                
                const marker = markerIcon 
                    ? L.marker([f.lat, f.lng], {icon: markerIcon}).addTo(map).bindPopup(f.name)
                    : L.marker([f.lat, f.lng]).addTo(map).bindPopup(f.name);
                
                // show share button when marker is clicked (same as clicking the list item)
                marker.on('click', function(){
                    map.flyTo(marker.getLatLng(), 16, {animate:true});
                    if (marker.openPopup) marker.openPopup();
                    // set selected id so the UI persists across reloads
                    selectedFavId = f.id;
                    if(shareBtn){
                        shareBtn.style.display = 'block';
                        shareBtn.dataset.favId = f.id;
                        shareBtn.dataset.favName = f.name;
                        shareBtn.dataset.favLat = f.lat;
                        shareBtn.dataset.favLng = f.lng;
                        if(shareMsg){ shareMsg.style.display = 'none'; shareMsg.textContent = ''; }
                    }
                    if(detailsBtn){
                        detailsBtn.style.display = 'block';
                        detailsBtn.dataset.favId = f.id;
                        detailsBtn.dataset.favName = f.name;
                        detailsBtn.dataset.favDescription = f.description || '';
                        detailsBtn.dataset.isCreator = f.is_creator ? 'true' : 'false';
                    }
                });
                markers[f.id] = marker;
            });
            renderFavoritesList(data);
        }catch(e){ console.error(e); }
    }

    saveBtn.addEventListener('click', async function(){
        const name = nameInput.value.trim();
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        const errorBox = document.getElementById('fav-error');
        if(errorBox){ errorBox.style.display = 'none'; errorBox.textContent = ''; }
        if(!name){ if(errorBox){ errorBox.textContent = 'Adj meg egy rövid nevet a helynek.'; errorBox.style.display = 'block'; nameInput.focus(); } return; }

        try{
            const res = await fetch(routes.favoritesStore, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name, lat, lng })
            });

            if(!res.ok){
                const err = await res.json().catch(()=>null);
                if(err && err.errors && err.errors.name && err.errors.name.length){
                    if(errorBox){ errorBox.textContent = err.errors.name[0]; errorBox.style.display = 'block'; nameInput.focus(); }
                    return;
                }
                console.error(err);
                return;
            }

            const saved = await res.json();

            if(tempMarker){
                try{ map.removeLayer(tempMarker); }catch(e){}
                tempMarker = null;
            }

            // reload favorites (markers + list) so the new item appears immediately
            await loadFavorites();
            favForm.style.display = 'none';
            nameInput.value = '';
            if(errorBox){ errorBox.style.display = 'none'; errorBox.textContent = ''; }
            setSelectionMode(false);
        }catch(e){ console.error(e); }
    });

    // share button behavior: open friends modal
    if(shareBtn){
        shareBtn.addEventListener('click', function(e){
            e.stopPropagation();
            friendsToShareTo = [];
            openShareModal();
        });
    }

    if (shareModalShare) {
        shareModalShare.addEventListener('click', async function(){
            if(friendsToShareTo.length === 0){
                alert('Válassz ki legalább egy barátot a megosztáshoz.');
                return;
            }
            
            if(!selectedFavId){
                alert('Nincs kiválasztott kedvenc hely.');
                return;
            }

            try {
                const friendIds = friendsToShareTo.map(f => f.id);
                const res = await fetch('/favorites/share', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        fav_id: selectedFavId,
                        friends: friendIds
                    })
                });

                if(!res.ok){
                    const err = await res.json().catch(() => null);
                    alert((err && err.message) || 'Hiba történt a megosztás közben.');
                    return;
                }

                const result = await res.json();
                
                // Close modal
                closeShareModal();
                
                // Show success/info message
                let message = '';
                if(result.shared && result.shared.length > 0){
                    message = `Sikeresen megosztva ${result.shared.length} baráttal: ${result.shared.map(f => f.name).join(', ')}`;
                }
                if(result.skipped && result.skipped.length > 0){
                    if(message) message += '\n\n';
                    message += `Nem lett megosztva (már van ilyen helyük): ${result.skipped.map(f => f.name).join(', ')}`;
                }
                
                if(message){
                    if(shareMsg){
                        shareMsg.textContent = message;
                        shareMsg.style.display = 'block';
                        shareMsg.style.color = result.shared.length > 0 ? '#059669' : '#dc2626';
                        shareMsg.style.marginTop = '0.5rem';
                        shareMsg.style.fontSize = '0.9rem';
                        shareMsg.style.whiteSpace = 'pre-line';
                        setTimeout(() => { if(shareMsg) shareMsg.style.display = 'none'; }, 5000);
                    } else {
                        alert(message);
                    }
                }
                
                // Hide share button
                if(shareBtn) shareBtn.style.display = 'none';
                if(detailsBtn) detailsBtn.style.display = 'none';
                selectedFavId = null;
                friendsToShareTo = [];
                
            } catch(err) {
                console.error('Share error:', err);
                alert('Hálózati hiba a megosztás közben.');
            }
        });
    }        

    if(shareModalClose) shareModalClose.addEventListener('click', closeShareModal);
    if(shareModalCancel) shareModalCancel.addEventListener('click', closeShareModal);
    if(shareModalBackdrop) shareModalBackdrop.addEventListener('click', function(e){ if(e.target === shareModalBackdrop) closeShareModal(); });

    // Details modal functions
    function openDetailsModal(favId, favName, favDescription, isCreator){
        currentDetailsPlace = {
            id: favId,
            name: favName,
            description: favDescription || '',
            isCreator: isCreator,
            originalDescription: favDescription || ''
        };
        
        document.getElementById('details-modal-title').textContent = favName;
        detailsDescription.value = currentDetailsPlace.description;
        detailsDescription.readOnly = true;
        detailsError.style.display = 'none';
        detailsError.textContent = '';
        
        // Show edit button only if user is creator
        if(detailsEditBtn){
            detailsEditBtn.style.display = isCreator ? 'block' : 'none';
        }
        if(detailsSaveBtn){
            detailsSaveBtn.style.display = 'none';
        }
        if(detailsCancelBtn){
            detailsCancelBtn.style.display = 'block'; 
        }
        
        detailsModalBackdrop.classList.add('show');
    }
    
    function closeDetailsModal(){
        detailsModalBackdrop.classList.remove('show');
        currentDetailsPlace = null;
        detailsDescription.readOnly = true;
        if(detailsEditBtn) detailsEditBtn.style.display = 'none';
        if(detailsSaveBtn) detailsSaveBtn.style.display = 'none';
        if(detailsCancelBtn) detailsCancelBtn.style.display = 'none';
    }
    
    function enterEditMode(){
        if(!currentDetailsPlace || !currentDetailsPlace.isCreator) return;
        
        detailsDescription.readOnly = false;
        detailsDescription.focus();
        detailsError.style.display = 'none';
        detailsError.textContent = '';
        
        if(detailsEditBtn) detailsEditBtn.style.display = 'none';
        if(detailsSaveBtn) detailsSaveBtn.style.display = 'block';
        if(detailsCancelBtn) detailsCancelBtn.style.display = 'block';
    }
    
    function cancelEditMode(){
        if(!currentDetailsPlace) return;
        
        // Check if we're in edit mode
        if(!detailsDescription.readOnly){
            detailsDescription.value = currentDetailsPlace.originalDescription;
            detailsDescription.readOnly = true;
            detailsError.style.display = 'none';
            detailsError.textContent = '';
            
            if(detailsEditBtn) detailsEditBtn.style.display = 'block';
            if(detailsSaveBtn) detailsSaveBtn.style.display = 'none';
            if(detailsCancelBtn) detailsCancelBtn.style.display = 'block'; // Keep visible as close button
        } else {
            closeDetailsModal();
        }
    }
    
    async function saveDescription(){
        if(!currentDetailsPlace) return;
        
        const newDescription = detailsDescription.value.trim();
        
        try {
            const response = await fetch(`/favorites/${currentDetailsPlace.id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ description: newDescription })
            });
            
            if(!response.ok){
                const data = await response.json();
                if(data.errors && data.errors.description){
                    detailsError.textContent = data.errors.description[0];
                    detailsError.style.display = 'block';
                } else {
                    detailsError.textContent = data.message || 'Hiba történt a mentés során.';
                    detailsError.style.display = 'block';
                }
                return;
            }
            
            // Update successful
            currentDetailsPlace.description = newDescription;
            currentDetailsPlace.originalDescription = newDescription;
            
            if(detailsBtn){
                detailsBtn.dataset.favDescription = newDescription;
            }
            
            detailsDescription.readOnly = true;
            detailsError.style.display = 'none';
            detailsError.textContent = '';
            
            if(detailsEditBtn) detailsEditBtn.style.display = 'block';
            if(detailsSaveBtn) detailsSaveBtn.style.display = 'none';
            if(detailsCancelBtn) detailsCancelBtn.style.display = 'block';
            
        } catch(err) {
            console.error('Save description error:', err);
            detailsError.textContent = 'Hálózati hiba a mentés során.';
            detailsError.style.display = 'block';
        }
    }
    
    if(detailsModalClose) detailsModalClose.addEventListener('click', closeDetailsModal);
    if(detailsModalBackdrop) detailsModalBackdrop.addEventListener('click', function(e){ 
        if(e.target === detailsModalBackdrop) closeDetailsModal(); 
    });
    if(detailsEditBtn) detailsEditBtn.addEventListener('click', enterEditMode);
    if(detailsCancelBtn) detailsCancelBtn.addEventListener('click', cancelEditMode);
    if(detailsSaveBtn) detailsSaveBtn.addEventListener('click', saveDescription);
    
    if(detailsBtn){
        detailsBtn.addEventListener('click', function(){
            const favId = detailsBtn.dataset.favId;
            const favName = detailsBtn.dataset.favName || '';
            const favDescription = detailsBtn.dataset.favDescription || '';
            const isCreator = detailsBtn.dataset.isCreator === 'true';
            
            if(favId){
                openDetailsModal(parseInt(favId), favName, favDescription, isCreator);
            }
        });
    }

    // initial load
    loadFavorites();

    // hide share and details buttons when a popup is closed
    map.on('popupclose', function(){
        if(shareBtn) {
            shareBtn.style.display = 'none';
        }
        if(detailsBtn) {
            detailsBtn.style.display = 'none';
        }
        if(shareMsg) { 
            shareMsg.style.display = 'none';
            shareMsg.textContent = '';
        }
    });
})();

document.addEventListener('DOMContentLoaded', function () {
    const giverSelect = document.getElementById('giver-select');
    const drawBtn = document.getElementById('draw-btn');
    const resultDiv = document.getElementById('result');
    const canvas = document.getElementById('wheel');
    const ctx = canvas.getContext('2d');
    const center = canvas.width / 2;
    const radius = canvas.width / 2 - 10;

    const groupId = document.getElementById('group-id').value;

    let wheelUsers = [];
    let spinning = false;
    const assignedReceivers = {};
    allUsers.forEach(u => {
        if(u.assignedTo) assignedReceivers[u.assignedTo] = true;
    });

    function drawWheel(rotation = 0) {
        ctx.clearRect(0,0,canvas.width,canvas.height);
        if(!wheelUsers.length){
            ctx.font='20px Arial';
            ctx.fillStyle='#333';
            ctx.textAlign='center';
            ctx.fillText('No participants', center, center);
            return;
        }
        const sliceAngle = (2 * Math.PI) / wheelUsers.length;

        // 12 colors
        const colors = [
            '#f39959',
            '#4f8ef3', 
            '#4ed8aa', 
            '#e9637a', 
            '#845be2', 
            '#ebcd58', 
            '#42cbe4', 
            '#ee68ab', 
            '#c5f17d', 
            '#f1c788', 
            '#6084fa', 
            '#34a6d3'  
        ];

        for (let i = 0; i < wheelUsers.length; i++) {
            const start = i * sliceAngle + rotation - Math.PI / 2;
            const end = start + sliceAngle;

            // Use the color from the palette based on index
            ctx.fillStyle = colors[i % colors.length];

            ctx.beginPath();
            ctx.moveTo(center, center);
            ctx.arc(center, center, radius, start, end);
            ctx.closePath();
            ctx.fill();

            // Draw participant name
            ctx.save();
            ctx.translate(center, center);
            ctx.rotate(start + sliceAngle / 2);
            ctx.textAlign = 'right';
            ctx.fillStyle = '#000';
            ctx.font = '16px Arial';
            ctx.fillText(wheelUsers[i].name, radius - 10, 5);
            ctx.restore();
        }

        drawPointer();
    }

    function drawPointer(){
        const arrowLength = 300;
        const arrowWidth = 150;
        const pointerDistance = radius-10;
        ctx.save();
        ctx.translate(center,center);
        ctx.beginPath();
        ctx.moveTo(0,pointerDistance);
        ctx.lineTo(-arrowWidth/2,pointerDistance+arrowLength);
        ctx.lineTo(arrowWidth/2,pointerDistance+arrowLength);
        ctx.closePath();
        ctx.fillStyle='red';
        ctx.fill();
        ctx.strokeStyle='darkred';
        ctx.stroke();
        ctx.restore();
    }

    function spinWheel(finalIndex){
        if(spinning) return;
        spinning=true;
        drawBtn.disabled=true;
        let rotation=0;
        let speed=Math.random()*0.3+0.3;
        const friction=0.995;
        const sliceAngle=(2*Math.PI)/wheelUsers.length;
        function animate(){
            rotation+=speed;
            speed*=friction;
            drawWheel(rotation);
            if(speed>0.002){
                requestAnimationFrame(animate);
            }else{
                const stopRotation = finalIndex*sliceAngle+sliceAngle/2;
                drawWheel(stopRotation);
                const receiver = wheelUsers[finalIndex];
                resultDiv.innerHTML=` Receiver: <strong>${receiver.name}</strong>`;
                assignReceiverToGiver(receiver.id,function(){
                    assignedReceivers[receiver.id]=true;
                    const giverOption=giverSelect.querySelector(`option[value="${giverSelect.value}"]`);
                    if(giverOption) giverOption.remove();
                    giverSelect.value="";
                    updateWheelUsers();
                    drawWheel();
                    spinning=false;
                    drawBtn.disabled=true;
                });
            }
        }
        animate();
    }

    function assignReceiverToGiver(receiverId, callback){
        const giverId=parseInt(giverSelect.value);
        if(!giverId) return;
        const xhr=new XMLHttpRequest();
        xhr.open("POST",`/exchange/admin/group/${groupId}/draw-assign`,true);
        xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        xhr.onreadystatechange=function(){
            if(xhr.readyState===4){
                if(xhr.status===200){
                    const data=JSON.parse(xhr.responseText);
                    if(data.success){callback();}
                    else if(data.error){alert(data.error); spinning=false; drawBtn.disabled=false;}
                } else {alert("Error assigning receiver."); spinning=false; drawBtn.disabled=false;}
            }
        };
        xhr.send(`giverId=${giverId}&receiverId=${receiverId}`);
    }

    function updateWheelUsers(){
        const giverId=parseInt(giverSelect.value);
        if(!giverId){wheelUsers=[]; drawBtn.disabled=true;}
        else{
            wheelUsers=allUsers.filter(u=>u.id!==giverId && !assignedReceivers[u.id]);
            drawBtn.disabled=wheelUsers.length===0;
        }
    }

    giverSelect.addEventListener('change',()=>{
        updateWheelUsers();
        drawWheel();
        resultDiv.innerHTML='';
    });

    drawBtn.addEventListener('click',()=>{
        if(!giverSelect.value){alert('Please select a giver!'); return;}
        if(!wheelUsers.length){alert('No participants available!'); return;}
        const finalIndex=Math.floor(Math.random()*wheelUsers.length);
        spinWheel(finalIndex);
    });

    updateWheelUsers();
    drawWheel();
});
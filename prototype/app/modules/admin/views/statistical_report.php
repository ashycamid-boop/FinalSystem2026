<?php
session_start();

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: /prototype/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Statistical Report</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Chart.js for data visualization -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <!-- Chart.js DataLabels Plugin for percentage display -->
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
  <!-- Admin common styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/common.css">
  <!-- Statistical Report specific styles -->
  <link rel="stylesheet" href="../../../../public/assets/css/modules/admin/statistical-report.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Fredoka+One:400&display=swap" rel="stylesheet">
  
  <style>
    :root{ --bg:#E8E8E8; --card:#fff; --ink:#111827; --muted:#6b7280; --brand:#0038A8; }
    /* Move main-content higher */
    .main-content {
      margin-top: -0px !important;
      padding-top: 0 !important;
    }
    
    .main {
      margin-top: -10px !important;
    }
    /* Topbar */
    .topbar{display:flex;align-items:center;justify-content:center;padding:px 0}
    .title{font-weight:800;color:var(--brand)}
    .controls{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .controls input,.controls select,.controls button,.controls label{
      border:1px solid #e5e7eb;border-radius:10px;padding:8px 12px;background:#fff
    }
    .controls button{border:none;background:var(--brand);color:#fff;font-weight:700;cursor:pointer}
    .controls .toggle{display:flex;gap:8px;align-items:center}

    /* Tabs */
    .tabs{width:95%;margin:8px auto}
    .tabbar{display:flex;gap:8px;flex-wrap:wrap}
    .tab{padding:10px 12px;border-radius:10px;background:#eef2ff;color:#1f2937;border:1px solid #e5e7eb;cursor:pointer}
    .tab.active{background:#dbeafe;border-color:#bfdbfe}

    /* KPIs */
    .kpis{display:grid;grid-template-columns:repeat(6,minmax(140px,1fr));gap:12px;margin:10px auto 12px;width:95%}
    .kpi{background:var(--card);border-radius:12px;padding:10px 12px;box-shadow:0 8px 18px rgba(0,0,0,.04)}
    .kpi .label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em}
    .kpi .value{font-size:20px;font-weight:800;margin-top:4px}

    /* Charts grid + cards */
    .grid{display:grid;gap:12px;grid-template-columns:1fr 1.2fr;width:95%;margin:0 auto}
    .card{background:var(--card);border-radius:12px;padding:12px 14px;box-shadow:0 8px 22px rgba(0,0,0,.05);position:relative;min-height:320px;overflow-x:auto}
    .card h3{margin:0 0 6px;font-size:15px}
    .card .actions{position:absolute;top:10px;right:8px}
    .iconbtn{border:none;background:#f3f4f6;border-radius:10px;padding:6px 8px;cursor:pointer}
    .card canvas{display:block !important;width:100% !important;height:280px !important;min-width:500px}

    /* "Show All" layout */
    .section{width:95%;margin:14px auto}
    .section h2{margin:10px 2px}

    /* Main Content Transitions */
    .main-content {
      opacity: 0;
      transform: translateY(30px) scale(0.98);
      filter: blur(2px);
      animation: mainContentFadeIn 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.2s forwards;
      transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    @keyframes mainContentFadeIn {
      0% {
        opacity: 0;
        transform: translateY(30px) scale(0.98);
        filter: blur(2px);
      }
      60% {
        opacity: 0.8;
        transform: translateY(10px) scale(0.99);
        filter: blur(1px);
      }
      100% {
        opacity: 1;
        transform: translateY(0) scale(1);
        filter: blur(0);
      }
    }
    
    /* Content Elements Staggered Animation */
    .main-content > * {
      opacity: 0;
      transform: translateY(20px);
      animation: contentElementFadeIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    }
    
    .main-content > *:nth-child(1) { animation-delay: 0.4s; }
    .main-content > *:nth-child(2) { animation-delay: 0.5s; }
    .main-content > *:nth-child(3) { animation-delay: 0.6s; }
    .main-content > *:nth-child(4) { animation-delay: 0.7s; }
    .main-content > *:nth-child(5) { animation-delay: 0.8s; }
    
    @keyframes contentElementFadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Smooth content transitions on data change */
    .main-content.updating {
      opacity: 0.6;
      transform: scale(0.98);
      filter: blur(1px);
    }
    
    .main-content.updated {
      opacity: 1;
      transform: scale(1);
      filter: blur(0);
    }

    /* Print */
    @media print{
      body{background:#fff}
      .sidebar,.topbar,.iconbtn{display:none !important}
      .layout{display:block}
      .tabs{display:none}
      .grid{grid-template-columns:1fr 1fr}
      .kpis{grid-template-columns:repeat(3,1fr)}
      .card canvas{height:200px !important;min-width:auto}
    }
  </style>
</head>
<body>
  <div class="layout">
    <!-- Sidebar -->
    <nav class="sidebar" role="navigation" aria-label="Main sidebar">
      <div class="sidebar-logo">
        <img src="../../../../public/assets/images/denr-logo.png" alt="DENR Logo">
        <span>CENRO</span>
      </div>
      <div class="sidebar-role">Administrator</div>
      <nav class="sidebar-nav" aria-label="Sidebar menu">
        <ul>
          <li><a href="dashboard.php"><i class="fa fa-th-large"></i> Dashboard</a></li>
          <li><a href="user_management.php"><i class="fa fa-users"></i> User Management</a></li>
          <li><a href="spot_reports.php"><i class="fa fa-file-text"></i> Spot Reports</a></li>
          <li><a href="case_management.php"><i class="fa fa-briefcase"></i> Case Management</a></li>
          <li><a href="apprehended_items.php"><i class="fa fa-archive"></i> Apprehended Items</a></li>
          <li><a href="equipment_management.php"><i class="fa fa-cogs"></i> Equipment Management</a></li>
          <li><a href="assignments.php"><i class="fa fa-tasks"></i> Assignments</a></li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="serviceDeskToggle" data-target="serviceDeskMenu">
              <i class="fa fa-headset"></i> Service Desk 
              <i class="fa fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu" id="serviceDeskMenu">
              <li><a href="new_requests.php">New Requests <span class="badge">2</span></a></li>
              <li><a href="ongoing_scheduled.php">Ongoing / Scheduled <span class="badge badge-blue">2</span></a></li>
              <li><a href="completed.php">Completed</a></li>
              <li><a href="all_requests.php">All Requests</a></li>
            </ul>
          </li>
          <li class="active"><a href="statistical_report.php"><i class="fa fa-chart-bar"></i> Statistical Report</a></li>
        </ul>
      </nav>
    </nav>
    <!-- Main -->
    <div class="main">
      <div class="topbar">
        <div class="topbar-card">
          <div class="topbar-title">Statistical Report</div>
          <?php include __DIR__ . '/../../shared/views/topbar_profile.php'; ?>
        </div>
      </div>
      <!-- Top Controls -->
      <div class="topbar">
        <div class="topbar-card"> 
          <div class="controls">
            <label>From:</label>
            <input type="month" id="from">
            <label>To:</label>
            <input type="month" id="to">
            <label>Granularity:</label>
            <select id="granularity">
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
            </select>
            <button id="generate">Generate</button>
            <div class="toggle">
              <input type="checkbox" id="showAll">
              <label for="showAll">Show All</label>
            </div>
            <button id="printBtn">Print</button>
            <button id="exportCsv">Export CSV</button>
          </div>
        </div>
      </div>
      <div class="main-content">
        <!-- Statistical Report Content -->
        <div style="padding: 20px;">

          <!-- Tabs (hidden when Show All) -->
          <div class="tabs" id="tabs">
            <div class="tabbar" id="tabbar">
              <div class="tab active" data-tab="spot">Spot Reports</div>
              <div class="tab" data-tab="cases">Case Management</div>
              <div class="tab" data-tab="app_individuals">Apprehended Individuals</div>
              <div class="tab" data-tab="app_vehicles">Apprehended Vehicles</div>
              <div class="tab" data-tab="app_items">Apprehended Items</div>
              <div class="tab" data-tab="locations">Locations</div>
              <div class="tab" data-tab="service">Service Desk</div>
            </div>
          </div>

          <!-- KPIs -->
          <div class="kpis" id="kpis"></div>

          <!-- Grid or Sections (JS will render here) -->
          <div id="host"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Admin Dashboard JavaScript -->
  <script src="../../../../public/assets/js/admin/dashboard.js"></script>
  <!-- Admin Navigation JavaScript -->
  <script src="../../../../public/assets/js/admin/navigation.js"></script>

  <script>
    /* ===== Chart defaults ===== */
    Chart.defaults.animation = false;
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.resizeDelay = 200;
    
    // Register the datalabels plugin
    Chart.register(ChartDataLabels);

    const PALETTE = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#06b6d4','#84cc16','#64748b'];
    const rgba = (hex,a=.35)=>{const m=/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);return `rgba(${parseInt(m[1],16)},${parseInt(m[2],16)},${parseInt(m[3],16)},${a})`};

    /* ===== Period helpers (From/To months) ===== */
    function monthsBetween(fromStr, toStr){
      const [fy,fm] = fromStr.split('-').map(Number);
      const [ty,tm] = toStr.split('-').map(Number);
      const start = new Date(fy, fm-1, 1);
      const end   = new Date(ty, tm-1, 1);
      const out=[];
      let cur = new Date(start);
      while (cur <= end){
        out.push({y:cur.getFullYear(),m:cur.getMonth()+1,label:`${cur.getFullYear()}-${String(cur.getMonth()+1).padStart(2,'0')}`});
        cur.setMonth(cur.getMonth()+1);
      }
      return out;
    }
    const toQuarter = m=>Math.floor((m-1)/3)+1;
    function aggregate(series, baseMonths, gran){
      if (gran==='monthly') return {labels: baseMonths.map(x=>x.label), data: series.slice(0, baseMonths.length)};
      if (gran==='quarterly'){
        const map=new Map();
        baseMonths.forEach((bm,i)=>{const k=`${bm.y} Q${toQuarter(bm.m)}`; map.set(k,(map.get(k)||0)+(series[i]||0));});
        return {labels:[...map.keys()], data:[...map.values()]};
      }
      const map=new Map();
      baseMonths.forEach((bm,i)=>{const k=`${bm.y}`; map.set(k,(map.get(k)||0)+(series[i]||0));});
      return {labels:[...map.keys()], data:[...map.values()]};
    }
    function sum(arr){ return arr.reduce((x,y)=>x+(+y||0),0); }
    const labelize = g => g[0].toUpperCase()+g.slice(1);

    /* ===== Dummy monthly data (24 months) aligned to your modules ===== */
    const synth = (len, base=5, sway=4, seed=1) => {
      let x = seed*97; const out=[];
      for(let i=0;i<len;i++){ x = (x*1103515245+12345) & 0x7fffffff; out.push(Math.max(0, Math.round(base + (x%11 - 5) * 0.5 + Math.sin((i+seed)/3)*sway))); }
      return out;
    };
    const monthsWindow = 24;

    // Spot Reports
    const m_spot_reports = synth(monthsWindow, 8, 5, 11);
    const spotStatusLabels=['Approved','Pending','Rejected'];
    const spotStatusCounts=[14,5,2];

    // Case Management statuses
    const m_cases_opened = synth(monthsWindow, 4, 3, 15);
    const caseStatusLabels=['Under Investigation','For Filing','Ongoing','Dismissed','Resolved'];
    const caseStatusCounts=[5,2,3,1,9];

    // Apprehended: Individuals
    const m_app_individuals = synth(monthsWindow, 2, 2, 7);
    const rolesLabels=['Driver','Helper','Owner','Cutter','Hauler'];
    const rolesCounts=[6,4,3,2,1];
    const genderLabels=['Male','Female'];
    const genderCounts=[10,6];

    // Apprehended: Vehicles
    const m_app_vehicles = synth(monthsWindow, 1, 1.2, 9);
    const vehicleStatusLabels=['For Custody','Forfeited','Disposed','Temporarily Released (Bonded)','Turned Over to Court/PNP','Released by Court Order','For Auction/Donation'];
    const vehicleStatusCounts=[3,2,1,4,5,2,3];

    // Apprehended: Items
    const m_app_items = synth(monthsWindow, 3, 2, 13);
    const itemTypeLabels=['Forest Products','Equipment'];
    const itemTypeCounts=[9,4];

    // Locations
    const locLabels = ['Nasipit','Buenavista','Carmen','RTR','Magallanes','Butuan'];
    const locCounts = [6,5,4,3,2,1];

    // Service Desk
    const svcStatusLabels=['Pending','Approved','Rejected','Completed'];
    const svcStatusCounts=[3,4,1,10];
    const svcDevicesLabels=['UPS','Desktop Computer','Laptop Computers','Printers','Scanners','Cameras','Drones','Biometric Devices'];
    const svcDevicesCounts=[2,4,3,9,1,2,3,1];

    /* ===== Chart builders (BAR / PIE only) ===== */
    const charts = {};
    const mkBar = (id, labels, data, colorIdx=0)=>{
      const canvas = document.getElementById(id);
      if (!canvas) return;
      
      // Reset canvas animation
      canvas.classList.remove('loaded');
      
      if (charts[id]) charts[id].destroy();
      
      // Add smooth chart transition
      canvas.style.opacity = '0.3';
      canvas.style.transform = 'scale(0.98)';
      
      charts[id]=new Chart(canvas,{
        type:'bar',
        data:{
          labels,
          datasets:[{
            data,
            backgroundColor: data.map((_, i) => {
              // Gradient colors for each bar
              const canvas = document.createElement('canvas');
              const ctx = canvas.getContext('2d');
              const gradient = ctx.createLinearGradient(0, 0, 0, 300);
              gradient.addColorStop(0, PALETTE[(colorIdx + i) % PALETTE.length]);
              gradient.addColorStop(1, rgba(PALETTE[(colorIdx + i) % PALETTE.length], 0.3));
              return gradient;
            }),
            borderColor: PALETTE[colorIdx],
            borderWidth: 0,
            borderRadius: 12,
            borderSkipped: false,
            barThickness: 'flex',
            maxBarThickness: 50,
            hoverBackgroundColor: data.map((_, i) => PALETTE[(colorIdx + i) % PALETTE.length]),
            hoverBorderColor: '#fff',
            hoverBorderWidth: 3,
            // Shadow effect
            shadowOffsetX: 3,
            shadowOffsetY: 3,
            shadowBlur: 10,
            shadowColor: 'rgba(0, 0, 0, 0.2)'
          }]
        },
        options:{
          responsive: true,
          maintainAspectRatio: false,
          plugins:{
            legend:{display:false},
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#fff',
              bodyColor: '#fff',
              borderColor: PALETTE[colorIdx],
              borderWidth: 1,
              cornerRadius: 8,
              displayColors: false,
              callbacks: {
                title: function(context) {
                  return context[0].label;
                },
                label: function(context) {
                  return `Count: ${context.raw}`;
                }
              }
            }
          },
          scales:{
            x:{
              grid:{
                display: false
              },
              ticks:{
                autoSkip: true,
                maxRotation: 45,
                color: '#6b7280',
                font: {
                  size: 11,
                  weight: '500'
                }
              },
              border: {
                display: false
              }
            },
            y:{
              beginAtZero: true,
              grid: {
                color: 'rgba(107, 114, 128, 0.1)',
                drawBorder: false
              },
              ticks:{
                precision: 0,
                color: '#6b7280',
                font: {
                  size: 11
                },
                padding: 10
              },
              border: {
                display: false
              }
            }
          },
          animation: {
            duration: 1500,
            easing: 'easeOutBounce',
            delay: (context) => {
              return context.dataIndex * 200; // Stagger animation
            },
            onProgress: (animation) => {
              // Add glow effect during animation
              const ctx = animation.chart.ctx;
              ctx.save();
              ctx.shadowColor = PALETTE[colorIdx];
              ctx.shadowBlur = 20;
              ctx.restore();
            },
            onComplete: () => {
              canvas.classList.add('loaded');
            }
          },
          interaction: {
            intersect: false,
            mode: 'index'
          }
        }
      });
      
      // Complete chart transition
      setTimeout(() => {
        canvas.style.opacity = '1';
        canvas.style.transform = 'scale(1)';
        canvas.classList.add('loaded');
      }, 300);
    };
    const mkPie = (id, labels, data, doughnut=false)=>{
      const canvas = document.getElementById(id);
      if (!canvas) return;
      
      // Reset canvas animation
      canvas.classList.remove('loaded');
      
      if (charts[id]) charts[id].destroy();
      
      // Add smooth chart transition
      canvas.style.opacity = '0.3';
      canvas.style.transform = 'scale(0.98)';
      
      const colors=labels.map((_,i)=>PALETTE[i%PALETTE.length]);
      charts[id]=new Chart(canvas,{
        type: doughnut?'doughnut':'pie',
        data:{labels,datasets:[{data,backgroundColor:colors}]},
        options:{
          plugins:{
            legend:{
              position:'right',
              align: 'center',
              labels: {
                usePointStyle: true,
                pointStyle: 'rect',
                padding: 15,
                font: {
                  size: 12
                },
                generateLabels: function(chart) {
                  const data = chart.data;
                  if (data.labels.length && data.datasets.length) {
                    const dataset = data.datasets[0];
                    const total = dataset.data.reduce((a, b) => a + b, 0);
                    return data.labels.map((label, i) => {
                      const value = dataset.data[i];
                      const percentage = ((value / total) * 100).toFixed(1);
                      return {
                        text: `${label}: ${value} (${percentage}%)`,
                        fillStyle: dataset.backgroundColor[i],
                        strokeStyle: dataset.backgroundColor[i],
                        pointStyle: 'rect',
                        hidden: false,
                        index: i
                      };
                    });
                  }
                  return [];
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.raw / total) * 100).toFixed(1);
                  return `${context.label}: ${context.raw} (${percentage}%)`;
                }
              }
            },
            datalabels: {
              display: true,
              color: '#fff',
              font: {
                weight: 'bold',
                size: 11
              },
              formatter: function(value, context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                // Show both count and percentage, but only if slice is >= 3% to avoid clutter
                return percentage >= 3 ? `${value}\n${percentage}%` : '';
              },
              textAlign: 'center',
              anchor: 'center',
              align: 'center'
            }
          },
          cutout: doughnut?'55%':0,
          animation: {
            duration: 1200,
            easing: 'easeOutBounce',
            onComplete: () => {
              canvas.classList.add('loaded');
            }
          }
        }
      });
      
      // Complete chart transition
      setTimeout(() => {
        canvas.style.opacity = '1';
        canvas.style.transform = 'scale(1)';
        canvas.classList.add('loaded');
      }, 300);
    };

    /* ===== UI wiring ===== */
    const tabsWrap = document.getElementById('tabs');
    const tabbar   = document.getElementById('tabbar');
    const kpisDiv  = document.getElementById('kpis');
    const host     = document.getElementById('host');
    const showAllEl= document.getElementById('showAll');
    const printBtn = document.getElementById('printBtn');
    const exportBtn= document.getElementById('exportCsv');

    let activeTab = 'spot';

    // Default dates = last 12 months
    (function initMonths(){
      const now=new Date(), y=now.getFullYear(), m=now.getMonth()+1;
      document.getElementById('to').value   = `${y}-${String(m).padStart(2,'0')}`;
      const fromDate = new Date(y, m-12, 1);
      document.getElementById('from').value = `${fromDate.getFullYear()}-${String(fromDate.getMonth()+1).padStart(2,'0')}`;
    })();

    document.getElementById('generate').addEventListener('click', render);
    tabbar.addEventListener('click', (e)=>{
      const btn = e.target.closest('.tab'); if(!btn) return;
      [...tabbar.children].forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      activeTab = btn.dataset.tab;
      render();
    });
    showAllEl.addEventListener('change', ()=>{
      tabsWrap.style.display = showAllEl.checked ? 'none' : '';
      render();
    });
    printBtn.addEventListener('click', ()=>window.print());
    exportBtn.addEventListener('click', ()=>exportCurrentCSV());

    // helpers
    function kpiStrip(obj){ return Object.entries(obj).map(([k,v])=>`
      <div class="kpi"><div class="label">${k}</div><div class="value">${v}</div></div>`).join(''); }
    const card = (id,title)=>`
      <div class="card">
        <h3>${title} <div class="actions"><button class="iconbtn" data-export="${id}">📊</button></div></h3>
        <canvas id="${id}"></canvas>
      </div>`;
    document.addEventListener('click',(e)=>{
      const btn=e.target.closest('[data-export]'); if(!btn) return;
      const id=btn.getAttribute('data-export');
      const a=document.createElement('a'); a.download=id+'.png'; a.href=charts[id].toBase64Image(); a.click();
    });

    function sliceForRange(series, baseMonths){
      const n = baseMonths.length;
      const start = Math.max(0, series.length - n);
      return series.slice(start, start+n);
    }

    function renderKPIsOverall(gran, baseMonths){
      const aSpot = aggregate(sliceForRange(m_spot_reports, baseMonths), baseMonths, gran);
      const aCases= aggregate(sliceForRange(m_cases_opened, baseMonths), baseMonths, gran);
      const aInd  = aggregate(sliceForRange(m_app_individuals, baseMonths), baseMonths, gran);
      const aVeh  = aggregate(sliceForRange(m_app_vehicles, baseMonths), baseMonths, gran);
      const aItm  = aggregate(sliceForRange(m_app_items, baseMonths), baseMonths, gran);

      kpisDiv.innerHTML = kpiStrip({
        'Total Spot Reports': sum(aSpot.data),
        'Total Cases Opened': sum(aCases.data),
        'Apprehended Individuals': sum(aInd.data),
        'Apprehended Vehicles': sum(aVeh.data),
        'Apprehended Items': sum(aItm.data),
        'Completed Service Requests': svcStatusCounts[3]
      });
    }

    /* ===== Export CSV (current view) ===== */
    function toCSV(rows){
      return rows.map(r=>r.map(v=>{
        const s = String(v);
        return /[",\n]/.test(s) ? `"${s.replace(/"/g,'""')}"` : s;
      }).join(',')).join('\n');
    }
    function downloadCSV(filename, csv){
      const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = filename;
      a.click();
      setTimeout(()=>URL.revokeObjectURL(a.href), 1000);
    }
    function exportCurrentCSV(){
      const gran = document.getElementById('granularity').value;
      const from = document.getElementById('from').value;
      const to   = document.getElementById('to').value;
      const baseMonths = monthsBetween(from, to);
      const scope = showAllEl.checked ? 'all' : activeTab;

      const sections = [];
      const header = [['CENRO Statistical Report'],
                      ['Generated On', new Date().toLocaleDateString()],
                      ['From', from], ['To', to], ['Exported At', new Date().toLocaleString()]];
      sections.push(header, [['']]);

      // Export current data
      const csv = toCSV(sections.flat());
      downloadCSV(`cenro_report_${scope}_${from}_to_${to}.csv`, csv);
    }

    /* ===== Render (Tabs mode OR Show-All) ===== */
    function render(){
      const gran = document.getElementById('granularity').value;
      const from = document.getElementById('from').value;
      const to   = document.getElementById('to').value;
      if (!from || !to){ alert('Please select From and To months.'); return; }

      const baseMonths = monthsBetween(from, to);
      if (baseMonths.length<1){ alert('Invalid month range.'); return; }
      if (baseMonths.length>monthsWindow){
        alert(`Data range limited to ${monthsWindow} months. Trimming...`);
        baseMonths.splice(monthsWindow);
      }

      // destroy old charts
      Object.values(charts).forEach(c=>c.destroy && c.destroy());
      for (const k in charts) delete charts[k];
      host.innerHTML='';

      // KPIs across modules
      renderKPIsOverall(gran, baseMonths);

      // aligned series for range
      const spotSeries = aggregate(sliceForRange(m_spot_reports, baseMonths), baseMonths, gran);
      const caseSeries = aggregate(sliceForRange(m_cases_opened, baseMonths), baseMonths, gran);
      const indSeries  = aggregate(sliceForRange(m_app_individuals, baseMonths), baseMonths, gran);
      const vehSeries  = aggregate(sliceForRange(m_app_vehicles, baseMonths), baseMonths, gran);
      const itemSeries = aggregate(sliceForRange(m_app_items, baseMonths), baseMonths, gran);

      const gridStart = `<div class="grid">`, gridEnd = `</div>`;

      if (!showAllEl.checked){
        // TAB MODE
        if (activeTab==='spot'){
          host.innerHTML = gridStart + card('spotBar',`Spot Reports Over Time (${labelize(gran)})`) + card('spotPie','Spot Report Status Distribution') + gridEnd;
          mkBar('spotBar', spotSeries.labels, spotSeries.data, 0);
          mkPie('spotPie', spotStatusLabels, spotStatusCounts, true);
        }
        else if (activeTab==='cases'){
          host.innerHTML = gridStart + card('caseBar',`Cases Opened Over Time (${labelize(gran)})`) + card('casePie','Case Status Distribution') + gridEnd;
          mkBar('caseBar', caseSeries.labels, caseSeries.data, 5);
          mkPie('casePie', caseStatusLabels, caseStatusCounts, true);
        }
        else if (activeTab==='app_individuals'){
          host.innerHTML = gridStart + card('indBar',`Individuals Apprehended Over Time (${labelize(gran)})`) + card('rolePie','Roles & Gender Distribution') + gridEnd;
          mkBar('indBar', indSeries.labels, indSeries.data, 3);
          mkPie('rolePie', rolesLabels.concat(genderLabels.map(g=>'Gender: '+g)), rolesCounts.concat(genderCounts), true);
        }
        else if (activeTab==='app_vehicles'){
          host.innerHTML = gridStart + card('vehBar',`Vehicles Apprehended Over Time (${labelize(gran)})`) + card('vehPie','Vehicle Status Distribution') + gridEnd;
          mkBar('vehBar', vehSeries.labels, vehSeries.data, 1);
          mkPie('vehPie', vehicleStatusLabels, vehicleStatusCounts, true);
        }
        else if (activeTab==='app_items'){
          host.innerHTML = gridStart + card('itmBar',`Items Apprehended Over Time (${labelize(gran)})`) + card('itmPie','Item Type Distribution') + gridEnd;
          mkBar('itmBar', itemSeries.labels, itemSeries.data, 6);
          mkPie('itmPie', itemTypeLabels, itemTypeCounts, true);
        }
        else if (activeTab==='locations'){
          host.innerHTML = gridStart + card('locBar','Violations by Location') + card('locPie','Location Distribution') + gridEnd;
          mkBar('locBar', locLabels, locCounts, 4);
          mkPie('locPie', locLabels, locCounts, false);
        }
        else if (activeTab==='service'){
          host.innerHTML = gridStart + card('svcStatusPie','Service Request Status') + card('svcDevPie','Device Types Serviced') + gridEnd;
          mkPie('svcStatusPie', svcStatusLabels, svcStatusCounts, true);
          mkPie('svcDevPie', svcDevicesLabels, svcDevicesCounts, false);
        }
      } else {
        // SHOW-ALL MODE
        const sectionsHTML = [];
        sectionsHTML.push(`<div class="section"><h2>Spot Reports</h2>${gridStart}${card('spotBar',`Spot Reports Over Time (${labelize(gran)})`)}${card('spotPie','Status Distribution')}${gridEnd}</div>`);
        sectionsHTML.push(`<div class="section"><h2>Case Management</h2>${gridStart}${card('caseBar',`Cases Opened Over Time (${labelize(gran)})`)}${card('casePie','Status Distribution')}${gridEnd}</div>`);
        sectionsHTML.push(`<div class="section"><h2>Apprehended Individuals</h2>${gridStart}${card('indBar',`Individuals Over Time (${labelize(gran)})`)}${card('rolePie','Roles & Gender')}${gridEnd}</div>`);
        sectionsHTML.push(`<div class="section"><h2>Apprehended Vehicles</h2>${gridStart}${card('vehBar',`Vehicles Over Time (${labelize(gran)})`)}${card('vehPie','Status Distribution')}${gridEnd}</div>`);
        sectionsHTML.push(`<div class="section"><h2>Apprehended Items</h2>${gridStart}${card('itmBar',`Items Over Time (${labelize(gran)})`)}${card('itmPie','Type Distribution')}${gridEnd}</div>`);
        sectionsHTML.push(`<div class="section"><h2>Locations</h2>${gridStart}${card('locBar','Violations by Location')}${card('locPie','Distribution')}${gridEnd}</div>`);
        sectionsHTML.push(`<div class="section"><h2>Service Desk</h2>${gridStart}${card('svcStatusPie','Request Status')}${card('svcDevPie','Device Types')}${gridEnd}</div>`);

        host.innerHTML = sectionsHTML.join('');

        mkBar('spotBar', spotSeries.labels, spotSeries.data, 0);
        mkPie('spotPie', spotStatusLabels, spotStatusCounts, true);
        mkBar('caseBar', caseSeries.labels, caseSeries.data, 5);
        mkPie('casePie', caseStatusLabels, caseStatusCounts, true);
        mkBar('indBar', indSeries.labels, indSeries.data, 3);
        mkPie('rolePie', rolesLabels.concat(genderLabels.map(g=>'Gender: '+g)), rolesCounts.concat(genderCounts), true);
        mkBar('vehBar', vehSeries.labels, vehSeries.data, 1);
        mkPie('vehPie', vehicleStatusLabels, vehicleStatusCounts, true);
        mkBar('itmBar', itemSeries.labels, itemSeries.data, 6);
        mkPie('itmPie', itemTypeLabels, itemTypeCounts, true);
        mkBar('locBar', locLabels, locCounts, 4);
        mkPie('locPie', locLabels, locCounts, false);
        mkPie('svcStatusPie', svcStatusLabels, svcStatusCounts, true);
        mkPie('svcDevPie', svcDevicesLabels, svcDevicesCounts, false);
      }
    }

    /* ===== Main Content Transition Functions ===== */
    
    // Add smooth transitions when content updates
    function addContentTransition() {
      const mainContent = document.querySelector('.main-content');
      if (mainContent) {
        mainContent.classList.add('updating');
        
        setTimeout(() => {
          mainContent.classList.remove('updating');
          mainContent.classList.add('updated');
          
          setTimeout(() => {
            mainContent.classList.remove('updated');
          }, 600);
        }, 200);
      }
    }
    
    // Override render function to include transitions
    const originalRender = render;
    render = function() {
      addContentTransition();
      setTimeout(() => {
        originalRender();
      }, 100);
    };
    
    // Add transitions to generate button
    document.getElementById('generate').addEventListener('click', () => {
      const generateBtn = document.getElementById('generate');
      const originalText = generateBtn.innerHTML;
      
      generateBtn.innerHTML = 'Generating...';
      generateBtn.disabled = true;
      generateBtn.style.transform = 'scale(0.95)';
      
      setTimeout(() => {
        render();
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
        generateBtn.style.transform = 'scale(1)';
      }, 800);
    });

    // initial render
    render();
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(()=>setTimeout(render,100));
    }
  </script>
</body>
</html>
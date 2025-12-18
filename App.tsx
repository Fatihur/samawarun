
import React, { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence, LayoutGroup } from 'framer-motion';

// --- Types ---

interface EventData {
  id: string;
  date: { month: string; day: string };
  title: string;
  location: string;
  description: string;
  fullDescription: string;
  image: string;
  categories: string[];
  price: string;
  perks: string[];
  startTime: string;
}

interface GalleryData {
  id: string;
  url: string;
  caption: string;
}

interface FeatureData {
  id: number;
  icon: string;
  title: string;
  description: string;
}

// --- Initial Data ---

const INITIAL_EVENTS: EventData[] = [
  {
    id: '1',
    date: { month: 'AGU', day: '25' },
    title: 'Samawa Half Marathon',
    location: 'City Center, Jakarta',
    description: 'Tantang dirimu di jarak 21K dengan rute perkotaan yang menawan.',
    fullDescription: 'Samawa Half Marathon adalah event tahunan terbesar kami yang melintasi jalanan protokol ibu kota. Rute yang steril dan pemandangan gedung pencakar langit memberikan pengalaman lari yang tak terlupakan.',
    image: 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?q=80&w=1000',
    categories: ['5K Fun Run', '10K Road Race', '21K Half Marathon'],
    price: 'Rp 250.000',
    perks: ['Jersey Eksklusif', 'BIB Number', 'Medali Finisher'],
    startTime: '05:00 WIB'
  },
  {
    id: '2',
    date: { month: 'SEP', day: '12' },
    title: 'Interval Training Session',
    location: 'Stadion Madya GBK',
    description: 'Sesi latihan kecepatan dipandu coach profesional untuk tingkatkan pace.',
    fullDescription: 'Latihan interval adalah kunci untuk lari yang lebih cepat. Dalam sesi ini, Anda akan dipandu oleh coach bersertifikat untuk melakukan drill kecepatan dan teknik lari yang benar.',
    image: 'https://images.unsplash.com/photo-1596462502278-27bfad450216?q=80&w=1000',
    categories: ['Beginner', 'Advanced'],
    price: 'Gratis',
    perks: ['Coaching Clinic', 'Hydration'],
    startTime: '06:00 WIB'
  }
];

const INITIAL_GALLERY: GalleryData[] = [
  { id: 'g1', url: 'https://images.unsplash.com/photo-1530143311094-34d807799e8f?q=80&w=1000', caption: 'Sunday Morning Run' },
  { id: 'g2', url: 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?q=80&w=1000', caption: 'Speed Session at Stadium' },
  { id: 'g3', url: 'https://images.unsplash.com/photo-1502904550040-7534597429ae?q=80&w=1000', caption: 'Community Meetup' },
  { id: 'g4', url: 'https://images.unsplash.com/photo-1486218119243-13883505764c?q=80&w=1000', caption: 'Marathon Finish Line' },
  { id: 'g5', url: 'https://images.unsplash.com/photo-1533387558662-ee6920aa906e?q=80&w=1000', caption: 'Group Workout' },
  { id: 'g6', url: 'https://images.unsplash.com/photo-1444491741275-3747c53c99b4?q=80&w=1000', caption: 'Evening City Run' }
];

const FEATURES: FeatureData[] = [
  { id: 1, icon: 'directions_run', title: 'Weekly Run', description: 'Lari santai rutin setiap Minggu pagi keliling kota.' },
  { id: 2, icon: 'fitness_center', title: 'Training Program', description: 'Program latihan interval & strength untuk performa.' },
  { id: 3, icon: 'groups', title: 'Networking', description: 'Bertemu teman baru dengan minat yang sama.' },
  { id: 4, icon: 'emoji_events', title: 'Event & Race', description: 'Partisipasi kolektif dalam lomba lari nasional.' }
];

const NAV_LINKS = [
  { name: 'Beranda', href: '#' },
  { name: 'Event', href: '#event' },
  { name: 'Gallery', href: '#gallery' },
  { name: 'Kontak', href: '#kontak' }
];

// --- Admin Component ---

const AdminDashboard: React.FC<{ 
  events: EventData[]; 
  gallery: GalleryData[];
  onAddEvent: (e: EventData) => void; 
  onDeleteEvent: (id: string) => void;
  onEditEvent: (e: EventData) => void;
  onAddGallery: (g: GalleryData) => void;
  onDeleteGallery: (id: string) => void;
  onExit: () => void;
}> = ({ events, gallery, onAddEvent, onDeleteEvent, onEditEvent, onAddGallery, onDeleteGallery, onExit }) => {
  const [activeTab, setActiveTab] = useState<'events' | 'gallery'>('events');
  const [showEventForm, setShowEventForm] = useState(false);
  const [showGalleryForm, setShowGalleryForm] = useState(false);
  const [editingEvent, setEditingEvent] = useState<EventData | null>(null);

  const [eventFormData, setEventFormData] = useState<Partial<EventData>>({
    title: '', location: '', description: '', fullDescription: '',
    image: '', price: '', startTime: '', date: { month: '', day: '' },
    categories: [], perks: []
  });

  const [galleryFormData, setGalleryFormData] = useState({ url: '', caption: '' });

  const handleEditClick = (e: EventData) => {
    setEditingEvent(e);
    setEventFormData(e);
    setShowEventForm(true);
  };

  const handleEventSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingEvent) {
      onEditEvent(eventFormData as EventData);
    } else {
      onAddEvent({ ...eventFormData, id: Date.now().toString() } as EventData);
    }
    setShowEventForm(false);
    setEditingEvent(null);
    setEventFormData({ title: '', location: '', description: '', fullDescription: '', image: '', price: '', startTime: '', date: { month: '', day: '' }, categories: [], perks: [] });
  };

  const handleGallerySubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onAddGallery({ ...galleryFormData, id: 'g' + Date.now().toString() });
    setShowGalleryForm(false);
    setGalleryFormData({ url: '', caption: '' });
  };

  return (
    <motion.div 
      initial={{ opacity: 0 }} 
      animate={{ opacity: 1 }} 
      exit={{ opacity: 0 }}
      className="min-h-screen bg-[#0a140e] text-white flex"
    >
      {/* Sidebar */}
      <aside className="w-64 border-r border-white/10 p-8 hidden lg:flex flex-col fixed h-full bg-[#0a140e] z-20">
        <div className="flex items-center gap-3 mb-12">
          <span className="material-symbols-outlined text-primary text-3xl">shield_person</span>
          <h2 className="text-xl font-black">Admin Panel</h2>
        </div>
        <nav className="flex flex-col gap-4">
          <button 
            onClick={() => setActiveTab('events')}
            className={`flex items-center gap-3 p-3 rounded-xl font-bold transition-all ${activeTab === 'events' ? 'bg-primary text-bg-dark' : 'text-gray-400 hover:bg-white/5'}`}
          >
            <span className="material-symbols-outlined">event</span> Events
          </button>
          <button 
            onClick={() => setActiveTab('gallery')}
            className={`flex items-center gap-3 p-3 rounded-xl font-bold transition-all ${activeTab === 'gallery' ? 'bg-primary text-bg-dark' : 'text-gray-400 hover:bg-white/5'}`}
          >
            <span className="material-symbols-outlined">photo_library</span> Gallery
          </button>
          <button className="flex items-center gap-3 p-3 rounded-xl text-gray-400 hover:bg-white/5"><span className="material-symbols-outlined">group</span> Members</button>
        </nav>
        <button onClick={onExit} className="mt-auto flex items-center gap-3 p-3 rounded-xl text-red-400 hover:bg-red-400/10 transition-colors"><span className="material-symbols-outlined">logout</span> Exit Admin</button>
      </aside>

      {/* Main Content */}
      <main className="flex-1 p-8 lg:p-12 lg:ml-64">
        <AnimatePresence mode="wait">
          {activeTab === 'events' ? (
            <motion.div 
              key="events-tab"
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
            >
              <header className="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                  <h1 className="text-3xl font-black">Manage Events</h1>
                  <p className="text-gray-500">Update, add, or remove community runs and races.</p>
                </div>
                <button onClick={() => { setEditingEvent(null); setEventFormData({ title: '', location: '', description: '', fullDescription: '', image: '', price: '', startTime: '', date: { month: '', day: '' }, categories: [], perks: [] }); setShowEventForm(true); }} className="flex items-center gap-2 bg-primary text-bg-dark px-6 py-3 rounded-xl font-bold hover:scale-105 transition-transform active:scale-95 shadow-lg shadow-primary/20">
                  <span className="material-symbols-outlined">add</span> Create Event
                </button>
              </header>

              <div className="bg-bg-surface rounded-2xl border border-white/10 overflow-hidden shadow-2xl">
                <table className="w-full text-left">
                  <thead>
                    <tr className="border-b border-white/5 bg-white/5">
                      <th className="p-5 text-xs font-bold uppercase text-gray-400">Event Name</th>
                      <th className="p-5 text-xs font-bold uppercase text-gray-400">Date</th>
                      <th className="p-5 text-xs font-bold uppercase text-gray-400">Price</th>
                      <th className="p-5 text-xs font-bold uppercase text-gray-400 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {events.map(event => (
                      <motion.tr layout key={event.id} className="border-b border-white/5 hover:bg-white/5 transition-colors group">
                        <td className="p-5 font-bold group-hover:text-primary transition-colors">{event.title}</td>
                        <td className="p-5 text-sm text-gray-400">{event.date.month} {event.date.day}</td>
                        <td className="p-5"><span className={`text-xs px-2 py-1 rounded-md font-bold ${event.price === 'Gratis' ? 'bg-blue-400/20 text-blue-400' : 'bg-primary/20 text-primary'}`}>{event.price}</span></td>
                        <td className="p-5 text-right flex justify-end gap-2">
                          <button onClick={() => handleEditClick(event)} className="h-9 w-9 flex items-center justify-center rounded-lg border border-white/10 hover:bg-primary hover:text-bg-dark transition-all"><span className="material-symbols-outlined text-sm">edit</span></button>
                          <button onClick={() => onDeleteEvent(event.id)} className="h-9 w-9 flex items-center justify-center rounded-lg border border-white/10 hover:bg-red-500 hover:text-white transition-all"><span className="material-symbols-outlined text-sm">delete</span></button>
                        </td>
                      </motion.tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </motion.div>
          ) : (
            <motion.div 
              key="gallery-tab"
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
            >
              <header className="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                  <h1 className="text-3xl font-black">Community Gallery</h1>
                  <p className="text-gray-500">Add or manage photos from community activities.</p>
                </div>
                <button onClick={() => setShowGalleryForm(true)} className="flex items-center gap-2 bg-primary text-bg-dark px-6 py-3 rounded-xl font-bold hover:scale-105 transition-transform active:scale-95 shadow-lg shadow-primary/20">
                  <span className="material-symbols-outlined">add_a_photo</span> Add Photo
                </button>
              </header>

              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <AnimatePresence>
                  {gallery.map(img => (
                    <motion.div 
                      layout
                      key={img.id} 
                      initial={{ scale: 0.8, opacity: 0 }}
                      animate={{ scale: 1, opacity: 1 }}
                      exit={{ scale: 0.8, opacity: 0 }}
                      className="relative aspect-square rounded-2xl overflow-hidden border border-white/10 group shadow-lg"
                    >
                      <img src={img.url} className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt={img.caption} />
                      <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3 p-4 text-center backdrop-blur-[2px]">
                        <p className="text-sm font-bold">{img.caption}</p>
                        <button onClick={() => onDeleteGallery(img.id)} className="bg-red-500 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-red-600 transition-colors shadow-lg">Remove</button>
                      </div>
                    </motion.div>
                  ))}
                </AnimatePresence>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </main>

      {/* Forms Modals */}
      <AnimatePresence>
        {(showEventForm || showGalleryForm) && (
          <motion.div 
            initial={{ opacity: 0 }} 
            animate={{ opacity: 1 }} 
            exit={{ opacity: 0 }} 
            className="fixed inset-0 z-[110] bg-bg-dark/95 flex items-center justify-center p-4 backdrop-blur-md"
          >
            {showEventForm && (
              <motion.div 
                initial={{ scale: 0.95, y: 20 }} 
                animate={{ scale: 1, y: 0 }} 
                className="bg-bg-surface w-full max-w-2xl rounded-[2.5rem] p-8 lg:p-10 border border-white/10 max-h-[90vh] overflow-y-auto no-scrollbar shadow-2xl"
              >
                <h2 className="text-3xl font-black mb-8 text-white">{editingEvent ? 'Edit Event' : 'Add New Event'}</h2>
                <form onSubmit={handleEventSubmit} className="grid grid-cols-2 gap-6">
                  <div className="col-span-2">
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Event Title</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={eventFormData.title} onChange={e => setEventFormData({...eventFormData, title: e.target.value})} />
                  </div>
                  <div>
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Month (e.g. OKT)</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={eventFormData.date?.month} onChange={e => setEventFormData({...eventFormData, date: {...eventFormData.date!, month: e.target.value}})} />
                  </div>
                  <div>
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Day (e.g. 15)</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={eventFormData.date?.day} onChange={e => setEventFormData({...eventFormData, date: {...eventFormData.date!, day: e.target.value}})} />
                  </div>
                  <div className="col-span-2">
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Image URL</label>
                    <input className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={eventFormData.image} onChange={e => setEventFormData({...eventFormData, image: e.target.value})} />
                  </div>
                  <div className="col-span-2">
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Location</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={eventFormData.location} onChange={e => setEventFormData({...eventFormData, location: e.target.value})} />
                  </div>
                  <div>
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Price</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={eventFormData.price} onChange={e => setEventFormData({...eventFormData, price: e.target.value})} />
                  </div>
                  <div>
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Start Time</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={eventFormData.startTime} onChange={e => setEventFormData({...eventFormData, startTime: e.target.value})} />
                  </div>
                  <div className="col-span-2 flex gap-4 pt-6">
                    <button type="submit" className="flex-1 bg-primary text-bg-dark font-black py-5 rounded-2xl hover:scale-[1.02] transition-transform active:scale-95 shadow-lg shadow-primary/20">Save Event</button>
                    <button type="button" onClick={() => setShowEventForm(false)} className="px-10 border border-white/10 rounded-2xl font-bold hover:bg-white/5 transition-colors">Cancel</button>
                  </div>
                </form>
              </motion.div>
            )}

            {showGalleryForm && (
              <motion.div 
                initial={{ scale: 0.95, y: 20 }} 
                animate={{ scale: 1, y: 0 }} 
                className="bg-bg-surface w-full max-w-md rounded-[2.5rem] p-8 lg:p-10 border border-white/10 shadow-2xl"
              >
                <h2 className="text-3xl font-black mb-8 text-white">Add Photo</h2>
                <form onSubmit={handleGallerySubmit} className="space-y-6">
                  <div>
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Image URL</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={galleryFormData.url} onChange={e => setGalleryFormData({...galleryFormData, url: e.target.value})} />
                  </div>
                  <div>
                    <label className="text-xs font-black text-gray-500 uppercase mb-2 block tracking-widest">Caption</label>
                    <input required className="w-full bg-bg-dark border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-primary transition-colors" value={galleryFormData.caption} onChange={e => setGalleryFormData({...galleryFormData, caption: e.target.value})} />
                  </div>
                  <div className="flex gap-4 pt-6">
                    <button type="submit" className="flex-1 bg-primary text-bg-dark font-black py-5 rounded-2xl hover:scale-[1.02] transition-transform active:scale-95 shadow-lg shadow-primary/20">Add Photo</button>
                    <button type="button" onClick={() => setShowGalleryForm(false)} className="px-8 border border-white/10 rounded-2xl font-bold hover:bg-white/5 transition-colors">Cancel</button>
                  </div>
                </form>
              </motion.div>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
};

// --- Landing Page Components ---

const EventDetailModal: React.FC<{ event: EventData; onClose: () => void }> = ({ event, onClose }) => {
  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      className="fixed inset-0 z-[100] flex items-center justify-center bg-bg-dark/95 p-4 backdrop-blur-md"
      onClick={onClose}
    >
      <motion.div
        initial={{ scale: 0.9, opacity: 0, y: 20 }}
        animate={{ scale: 1, opacity: 1, y: 0 }}
        exit={{ scale: 0.9, opacity: 0, y: 20 }}
        className="relative w-full max-w-4xl overflow-hidden rounded-[2.5rem] bg-bg-surface border border-white/10 shadow-2xl no-scrollbar max-h-[90vh] overflow-y-auto"
        onClick={(e) => e.stopPropagation()}
      >
        <button
          onClick={onClose}
          className="absolute right-6 top-6 z-20 flex h-12 w-12 items-center justify-center rounded-full bg-bg-dark/50 text-white backdrop-blur-md hover:bg-white/20 transition-colors shadow-lg"
        >
          <span className="material-symbols-outlined">close</span>
        </button>

        <div className="flex flex-col lg:flex-row">
          <div className="lg:w-1/2 h-64 lg:h-auto overflow-hidden">
            <img src={event.image || 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?q=80&w=1000'} className="h-full w-full object-cover" alt={event.title} />
          </div>
          <div className="flex flex-1 flex-col p-8 lg:p-12">
            <div className="mb-8 flex items-center gap-4">
               <div className="rounded-2xl bg-primary/20 px-4 py-2 text-center border border-primary/20 shadow-inner">
                  <p className="text-xs font-black text-primary uppercase">{event.date.month}</p>
                  <p className="text-3xl font-black text-white">{event.date.day}</p>
               </div>
               <h2 className="text-3xl lg:text-4xl font-black text-white tracking-tight">{event.title}</h2>
            </div>

            <div className="mb-8 space-y-6">
              <div className="flex items-start gap-4">
                <div className="h-10 w-10 flex items-center justify-center rounded-xl bg-primary/10 text-primary"><span className="material-symbols-outlined">location_on</span></div>
                <div>
                  <p className="text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Location</p>
                  <p className="text-white font-bold">{event.location}</p>
                </div>
              </div>
              <div className="grid grid-cols-2 gap-8">
                <div className="flex items-start gap-4">
                  <div className="h-10 w-10 flex items-center justify-center rounded-xl bg-primary/10 text-primary"><span className="material-symbols-outlined">schedule</span></div>
                  <div>
                    <p className="text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Time</p>
                    <p className="text-white font-bold">{event.startTime}</p>
                  </div>
                </div>
                <div className="flex items-start gap-4">
                  <div className="h-10 w-10 flex items-center justify-center rounded-xl bg-primary/10 text-primary"><span className="material-symbols-outlined">payments</span></div>
                  <div>
                    <p className="text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Registration</p>
                    <p className="text-white font-bold">{event.price}</p>
                  </div>
                </div>
              </div>
            </div>

            <div className="mb-10">
              <p className="text-gray-400 leading-relaxed text-lg mb-8">{event.fullDescription || event.description}</p>
              
              <div className="space-y-6">
                 <div>
                   <p className="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Categories</p>
                   <div className="flex flex-wrap gap-2">
                     {event.categories?.map(cat => <span key={cat} className="bg-white/5 border border-white/10 px-4 py-2 rounded-xl text-xs text-white font-bold uppercase tracking-wider">{cat}</span>)}
                   </div>
                 </div>
                 {event.perks && (
                   <div>
                     <p className="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">What's Included</p>
                     <div className="flex flex-wrap gap-2">
                       {event.perks.map(perk => <span key={perk} className="bg-primary/10 border border-primary/20 px-4 py-2 rounded-xl text-xs text-primary font-bold uppercase tracking-wider">{perk}</span>)}
                     </div>
                   </div>
                 )}
              </div>
            </div>

            <button className="w-full bg-primary text-bg-dark py-6 rounded-2xl text-xl font-black hover:scale-[1.02] active:scale-[0.98] transition-all shadow-xl shadow-primary/20">DAFTAR SEKARANG</button>
          </div>
        </div>
      </motion.div>
    </motion.div>
  );
};

const GallerySection: React.FC<{ items: GalleryData[] }> = ({ items }) => (
  <section className="px-6 py-24 lg:px-40 bg-bg-dark/50" id="gallery">
    <div className="max-w-7xl mx-auto">
      <div className="text-center mb-16">
        <motion.h2 initial={{ opacity: 0, y: 10 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-primary text-sm font-black uppercase tracking-[0.5em] mb-4">Momen Berharga</motion.h2>
        <motion.h2 initial={{ opacity: 0, y: 10 }} whileInView={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }} viewport={{ once: true }} className="text-white text-5xl lg:text-7xl font-black tracking-tight">Gallery Komunitas</motion.h2>
      </div>
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        {items.map((item, idx) => (
          <motion.div
            key={item.id}
            initial={{ opacity: 0, scale: 0.9 }}
            whileInView={{ opacity: 1, scale: 1 }}
            transition={{ delay: idx * 0.05 }}
            viewport={{ once: true }}
            whileHover={{ y: -5 }}
            className={`relative overflow-hidden rounded-[2rem] group cursor-pointer aspect-square ${idx % 5 === 0 ? 'md:col-span-2 md:aspect-auto md:h-80' : ''}`}
          >
            <img src={item.url} alt={item.caption} className="h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110" />
            <div className="absolute inset-0 bg-gradient-to-t from-bg-dark/90 via-bg-dark/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-8 flex items-end backdrop-blur-[1px]">
              <p className="text-white font-bold text-xl leading-tight">{item.caption}</p>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  </section>
);

const EventCard: React.FC<{ event: EventData; index: number; onSelect: (e: EventData) => void }> = ({ event, index, onSelect }) => (
  <motion.div
    initial={{ opacity: 0, y: 30 }} 
    whileInView={{ opacity: 1, y: 0 }} 
    transition={{ delay: index * 0.1, duration: 0.5 }} 
    viewport={{ once: true }}
    whileHover={{ y: -10 }}
    className="flex flex-col overflow-hidden rounded-[2.5rem] bg-bg-surface border border-white/5 group hover:border-primary/20 transition-all h-full shadow-2xl"
  >
    <div className="relative h-56 w-full overflow-hidden">
      <div className="absolute top-4 left-4 z-10 rounded-2xl bg-bg-dark/80 backdrop-blur-md px-4 py-2 text-center border border-white/10 shadow-lg">
        <p className="text-[10px] font-black text-primary uppercase tracking-widest">{event.date.month}</p>
        <p className="text-2xl font-black text-white leading-none">{event.date.day}</p>
      </div>
      <div className="h-full w-full bg-cover bg-center transition-transform duration-1000 group-hover:scale-110 cursor-pointer" style={{ backgroundImage: `url("${event.image || 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?q=80&w=1000'}")` }} onClick={() => onSelect(event)} />
    </div>
    <div className="flex flex-1 flex-col p-8">
      <h3 className="text-2xl font-black text-white mb-3 cursor-pointer hover:text-primary transition-colors leading-tight" onClick={() => onSelect(event)}>{event.title}</h3>
      <div className="flex items-center gap-2 text-gray-400 text-sm mb-6 font-medium"><span className="material-symbols-outlined text-primary text-xl">location_on</span><span>{event.location}</span></div>
      <p className="text-gray-400 text-base mb-8 line-clamp-2 leading-relaxed">{event.description}</p>
      <div className="mt-auto flex gap-3">
        <button onClick={() => onSelect(event)} className="flex-1 rounded-2xl bg-primary py-4 text-sm font-black text-bg-dark hover:bg-primary/90 transition-all shadow-lg shadow-primary/10 active:scale-95">Lihat Detail</button>
        <button onClick={() => onSelect(event)} className="h-12 w-12 flex items-center justify-center rounded-2xl border border-white/10 text-white hover:bg-white/5 transition-all active:scale-95"><span className="material-symbols-outlined">info</span></button>
      </div>
    </div>
  </motion.div>
);

const Header: React.FC = () => {
  const [scrolled, setScrolled] = useState(false);
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <>
      <header className={`fixed top-0 left-0 right-0 z-[60] flex items-center justify-between transition-all duration-700 px-6 py-4 lg:px-20 ${scrolled ? 'bg-bg-dark/80 backdrop-blur-2xl border-b border-white/5 py-3' : 'bg-transparent py-6'}`}>
        <div className="flex items-center gap-3 text-white group cursor-pointer" onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}>
          <div className="bg-primary/20 p-2 rounded-xl group-hover:rotate-12 transition-transform duration-500"><span className="material-symbols-outlined text-primary text-3xl">sprint</span></div>
          <h2 className="text-2xl font-black lg:text-3xl tracking-tighter">Samawa<span className="text-primary italic">Run</span></h2>
        </div>
        <div className="hidden lg:flex flex-1 justify-end gap-12 items-center">
          <nav className="flex items-center gap-10">
            {NAV_LINKS.map(l => <a key={l.name} className="text-white/60 hover:text-primary text-sm font-black tracking-widest uppercase transition-colors" href={l.href}>{l.name}</a>)}
          </nav>
          <button className="bg-primary text-bg-dark px-8 py-3.5 rounded-2xl text-sm font-black shadow-2xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">JOIN NOW</button>
        </div>
        <button className="lg:hidden h-12 w-12 flex items-center justify-center rounded-2xl bg-white/5 text-white" onClick={() => setIsMenuOpen(!isMenuOpen)}><span className="material-symbols-outlined text-3xl">{isMenuOpen ? 'close' : 'menu'}</span></button>
      </header>
      <AnimatePresence>
        {isMenuOpen && (
          <motion.div initial={{ opacity: 0, x: '100%' }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: '100%' }} transition={{ type: 'spring', damping: 25, stiffness: 200 }} className="fixed inset-0 z-[55] bg-bg-dark flex flex-col items-center justify-center p-8 lg:hidden">
             <div className="absolute inset-0 opacity-5 bg-[radial-gradient(circle_at_center,#30e87a_0%,transparent_70%)]"></div>
            <nav className="flex flex-col items-center gap-10 relative z-10">
              {NAV_LINKS.map((l, i) => (
                <motion.a 
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: i * 0.1 }}
                  key={l.name} 
                  onClick={() => setIsMenuOpen(false)} 
                  className="text-white text-5xl font-black tracking-tighter hover:text-primary transition-colors" 
                  href={l.href}
                >
                  {l.name}
                </motion.a>
              ))}
              <motion.button initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} transition={{ delay: 0.4 }} className="w-full mt-6 h-20 rounded-[2rem] bg-primary text-bg-dark text-2xl font-black shadow-2xl shadow-primary/20">GABUNG SEKARANG</motion.button>
            </nav>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
};

const Footer: React.FC<{ onAdminEnter: () => void }> = ({ onAdminEnter }) => (
  <footer className="border-t border-white/5 bg-[#0d1a12] pt-24 pb-12" id="kontak">
    <div className="px-6 lg:px-40 max-w-7xl mx-auto">
      <div className="flex flex-col gap-16 lg:flex-row lg:justify-between">
        <div className="flex flex-col gap-6 lg:max-w-md">
          <div className="flex items-center gap-3 text-white"><span className="material-symbols-outlined text-primary text-4xl">sprint</span><h2 className="text-3xl font-black tracking-tight">SamawaRun</h2></div>
          <p className="text-gray-400 text-lg leading-relaxed">Komunitas lari yang didedikasikan untuk membangun kesehatan fisik dan mental melalui kebersamaan. Langkah demi langkah, bersama kita melampaui batas.</p>
        </div>
        <div className="flex flex-wrap gap-16 lg:gap-32">
          <div className="flex flex-col gap-6">
            <h3 className="text-white font-black text-sm uppercase tracking-widest text-primary">Menu</h3>
            <div className="flex flex-col gap-4">
               {['Tentang Kami', 'Event', 'Member'].map(l => <a key={l} className="text-gray-400 hover:text-white text-lg font-bold transition-colors" href="#">{l}</a>)}
            </div>
          </div>
          <div className="flex flex-col gap-6">
            <h3 className="text-white font-black text-sm uppercase tracking-widest text-primary">Social</h3>
            <div className="flex gap-4">
              {[
                { i: 'public', n: 'Website' },
                { i: 'photo_camera', n: 'Instagram' },
                { i: 'chat', n: 'WhatsApp' }
              ].map(s => (
                <a key={s.i} className="h-14 w-14 flex items-center justify-center rounded-2xl bg-white/5 text-white hover:bg-primary hover:text-bg-dark transition-all duration-500 shadow-lg" href="#" title={s.n}>
                  <span className="material-symbols-outlined text-2xl">{s.i}</span>
                </a>
              ))}
            </div>
          </div>
        </div>
      </div>
      <div className="mt-24 flex flex-col md:flex-row items-center justify-between border-t border-white/5 pt-12 gap-8">
        <p className="text-gray-500 text-sm font-medium">© 2024 SamawaRun Community. Proudly powered by the streets.</p>
        <button onClick={onAdminEnter} className="text-gray-800 hover:text-primary transition-all text-xs font-black uppercase tracking-widest flex items-center gap-2 opacity-30 hover:opacity-100 bg-white/5 px-4 py-2 rounded-xl">
          <span className="material-symbols-outlined text-sm">admin_panel_settings</span> Control Panel
        </button>
      </div>
    </div>
  </footer>
);

// --- Main App Controller ---

export default function App() {
  const [view, setView] = useState<'user' | 'admin'>('user');
  const [events, setEvents] = useState<EventData[]>([]);
  const [gallery, setGallery] = useState<GalleryData[]>([]);
  const [selectedEvent, setSelectedEvent] = useState<EventData | null>(null);

  useEffect(() => {
    const savedEvents = localStorage.getItem('samawarun_events');
    const savedGallery = localStorage.getItem('samawarun_gallery');
    
    if (savedEvents) setEvents(JSON.parse(savedEvents));
    else { setEvents(INITIAL_EVENTS); localStorage.setItem('samawarun_events', JSON.stringify(INITIAL_EVENTS)); }
    
    if (savedGallery) setGallery(JSON.parse(savedGallery));
    else { setGallery(INITIAL_GALLERY); localStorage.setItem('samawarun_gallery', JSON.stringify(INITIAL_GALLERY)); }
  }, []);

  const saveEvents = (newEvents: EventData[]) => { setEvents(newEvents); localStorage.setItem('samawarun_events', JSON.stringify(newEvents)); };
  const saveGallery = (newGallery: GalleryData[]) => { setGallery(newGallery); localStorage.setItem('samawarun_gallery', JSON.stringify(newGallery)); };

  return (
    <div className="relative flex min-h-screen flex-col overflow-x-hidden selection:bg-primary selection:text-bg-dark">
      <AnimatePresence mode="wait">
        {view === 'admin' ? (
          <AdminDashboard 
            key="admin-view"
            events={events} 
            gallery={gallery}
            onAddEvent={(e) => saveEvents([e, ...events])} 
            onDeleteEvent={(id) => saveEvents(events.filter(e => e.id !== id))} 
            onEditEvent={(e) => saveEvents(events.map(ev => ev.id === e.id ? e : ev))}
            onAddGallery={(g) => saveGallery([g, ...gallery])}
            onDeleteGallery={(id) => saveGallery(gallery.filter(g => g.id !== id))}
            onExit={() => setView('user')} 
          />
        ) : (
          <motion.div 
            key="user-view"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="w-full flex flex-col"
          >
            <Header />
            <main className="flex-grow">
              {/* Hero */}
              <section className="relative flex min-h-[100vh] w-full flex-col items-center justify-center px-4 pt-20 overflow-hidden">
                <motion.div 
                  initial={{ scale: 1.1, opacity: 0 }}
                  animate={{ scale: 1, opacity: 1 }}
                  transition={{ duration: 1.5, ease: "easeOut" }}
                  className="absolute inset-0 z-0 h-full w-full bg-cover bg-center" 
                  style={{ backgroundImage: 'linear-gradient(rgba(17, 33, 23, 0.6) 0%, rgba(17, 33, 23, 0.98) 100%), url("https://images.unsplash.com/photo-1571008887538-b36bb32f4571?q=80&w=2000")' }} 
                />
                <motion.div initial={{ opacity: 0, y: 50 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8, delay: 0.2 }} className="relative z-10 text-center max-w-5xl px-4 flex flex-col gap-8">
                  <div className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 backdrop-blur-md px-6 py-2 self-center shadow-xl">
                    <span className="relative flex h-3 w-3"><span className="animate-ping absolute h-full w-full rounded-full bg-primary opacity-75"></span><span className="relative h-3 w-3 rounded-full bg-primary"></span></span>
                    <span className="text-xs font-black text-white uppercase tracking-[0.2em]">Join the movement</span>
                  </div>
                  <h1 className="text-white text-6xl font-black leading-[0.95] tracking-tighter lg:text-[10rem]">LARI BERSAMA,<br/><span className="text-primary italic">TUMBUH BERSAMA</span></h1>
                  <p className="text-gray-300 text-xl lg:text-3xl max-w-4xl mx-auto font-medium opacity-90 leading-relaxed">Setiap langkah adalah pencapaian yang kita rayakan bersama di lintasan aspal kota.</p>
                  <div className="mt-10 flex flex-wrap justify-center gap-6">
                    <button className="bg-primary text-bg-dark px-12 py-6 rounded-[2rem] text-2xl font-black shadow-2xl shadow-primary/20 hover:scale-105 active:scale-95 transition-all">GABUNG SEKARANG</button>
                    <a href="#event" className="bg-white/10 border border-white/20 text-white px-12 py-6 rounded-[2rem] text-2xl font-black backdrop-blur-md hover:bg-white/20 transition-all active:scale-95">LIHAT EVENT</a>
                  </div>
                </motion.div>
                <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 1, duration: 1 }} className="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce"><span className="material-symbols-outlined text-white/30 text-4xl">expand_more</span></motion.div>
              </section>

              {/* Features */}
              <section className="bg-bg-dark px-6 py-32 lg:px-40 border-y border-white/5 relative">
                <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 max-w-7xl mx-auto">
                  {FEATURES.map((f, i) => (
                    <motion.div 
                      key={f.id} 
                      initial={{ opacity: 0, y: 20 }} 
                      whileInView={{ opacity: 1, y: 0 }} 
                      transition={{ delay: i * 0.1 }} 
                      viewport={{ once: true }} 
                      className="bg-bg-surface border border-white/5 p-8 rounded-[2rem] hover:border-primary/20 transition-all duration-500 hover:shadow-2xl"
                    >
                      <div className="bg-primary/10 text-primary h-16 w-16 rounded-2xl flex items-center justify-center mb-6 shadow-inner"><span className="material-symbols-outlined text-3xl">{f.icon}</span></div>
                      <h3 className="text-white font-black text-2xl mb-4 tracking-tight">{f.title}</h3>
                      <p className="text-gray-400 text-lg leading-relaxed">{f.description}</p>
                    </motion.div>
                  ))}
                </div>
              </section>

              {/* Events */}
              <section className="px-6 py-32 lg:px-40 bg-bg-dark relative" id="event">
                <div className="max-w-7xl mx-auto">
                  <div className="mb-20">
                    <motion.h2 initial={{ opacity: 0, x: -20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }} className="text-primary text-sm font-black uppercase tracking-[0.5em] mb-4">Agenda Mendatang</motion.h2>
                    <motion.h2 initial={{ opacity: 0, x: -20 }} whileInView={{ opacity: 1, x: 0 }} transition={{ delay: 0.1 }} viewport={{ once: true }} className="text-white text-5xl lg:text-7xl font-black tracking-tighter">Event & Race</motion.h2>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                    {events.map((e, idx) => <EventCard key={e.id} event={e} index={idx} onSelect={setSelectedEvent} />)}
                    {events.length === 0 && <p className="text-gray-500 italic col-span-full py-20 text-center text-2xl">Belum ada event tersedia saat ini.</p>}
                  </div>
                </div>
              </section>

              {/* Gallery */}
              <GallerySection items={gallery} />

              {/* CTA */}
              <section className="py-40 px-6">
                <motion.div 
                  initial={{ opacity: 0, scale: 0.95 }} 
                  whileInView={{ opacity: 1, scale: 1 }} 
                  viewport={{ once: true }}
                  className="mx-auto max-w-7xl rounded-[4rem] bg-primary relative p-16 lg:p-32 text-center overflow-hidden shadow-2xl shadow-primary/20"
                >
                   <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#000_2px,transparent_2px)] [background-size:32px_32px]"></div>
                   <motion.h2 initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-bg-dark text-5xl lg:text-[7rem] font-black mb-12 relative z-10 leading-[0.95] tracking-tighter">APA LAGI YANG<br/>KAMU TUNGGU?</motion.h2>
                   <motion.button initial={{ opacity: 0, scale: 0.9 }} whileInView={{ opacity: 1, scale: 1 }} transition={{ delay: 0.2 }} viewport={{ once: true }} className="bg-bg-dark text-white px-16 py-8 rounded-[2.5rem] text-3xl font-black shadow-2xl relative z-10 hover:scale-105 active:scale-95 transition-all">GABUNG SEKARANG</motion.button>
                </motion.div>
              </section>
            </main>
            <Footer onAdminEnter={() => setView('admin')} />
          </motion.div>
        )}
      </AnimatePresence>

      <AnimatePresence>
        {selectedEvent && <EventDetailModal event={selectedEvent} onClose={() => setSelectedEvent(null)} />}
      </AnimatePresence>
    </div>
  );
}

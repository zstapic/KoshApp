import { Component, signal } from '@angular/core';
//import { RouterOutlet } from '@angular/router';

@Component({
  //imports: [RouterOutlet],
  selector: 'app-root',
  styleUrl: './app.scss',
  templateUrl: './app.html',
})
export class App {
  protected readonly title = signal('web');

  async dohvatiPodatke(): Promise<void> {
    
    try {
      const odgovor = await fetch('http://127.0.0.1:8000/');
      
      if (odgovor.ok) {
        const podaci: string = await odgovor.json();
        console.log(podaci);
      } 
    } catch (error) {
      console.error('Greška:', error);
    }
  }
}

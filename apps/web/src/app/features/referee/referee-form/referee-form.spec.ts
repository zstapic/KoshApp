import { ComponentFixture, TestBed } from '@angular/core/testing';
import { RefereeForm } from './referee-form';

describe('RefereeForm', () => {
  let component: RefereeForm;
  let fixture: ComponentFixture<RefereeForm>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RefereeForm],
    }).compileComponents();

    fixture = TestBed.createComponent(RefereeForm);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
